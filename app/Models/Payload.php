<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AssetNotFoundException;
use Exception;

class Payload //extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];
    protected $casts = [ 'rewards' => 'array', ];

    public $_name    = "Abstract class - do not use";
    public $_tags = [];

    function __construct() {
        $this->name           = $this->_name;
        $this->tags        = $this->_tags;
    }
    
    public function onAttackComplete($attack){

    }

    public static function getAll(){
        $dir = opendir(dirname(__FILE__)."/Payloads");
        while(($payload = readdir($dir)) !== false){
            if($payload != "." && $payload != ".."){
                $length = strlen($payload);
                $payload = substr($payload, 0, $payload - 4);
                $class = "\\App\\Models\\Payloads\\" . $payload;
                $payload[] = new $class();
            }
        }
        if(count($payload) == 0){
            throw new AssetNotFoundException();
        }
        return $payload;
    }

    public static function get($name){
        $payloads = Payload::getAll();
        foreach($payloads as $payload){
            if($payload->name == $name){
                return $payload;
            }
        }
        throw new AssetNotFoundException();
    }

}
