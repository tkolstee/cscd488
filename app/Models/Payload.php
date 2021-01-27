<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\AssetNotFoundException;

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
    protected $casts = [ 'tags' => 'array', ];

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
                $payload = substr($payload, 0, $length - 4);
                $class = "\\App\\Models\\Payloads\\" . $payload;
                $payloads[] = new $class();
            }
        }
        return $payloads;
    }

    public static function getByTag($tag) {
        $payloads = Payload::getAll();
        $result = [];
        foreach($payloads as $payload){
            if (in_array($tag, $payload->tags)) {
                $result[] = $payload;
            }
        }
        return $result;
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
