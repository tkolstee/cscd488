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
    public $_class_name = "Payload";
    public $_percentRevLost = 0;
    public $_percentRepLost = 0;
    public $_percentIncreasedSuccess = 0;
    public $_tags = [];

    function __construct() {
        $this->name        = $this->_name;
        $this->class_name  = $this->_class_name;
        $this->percentRevLost = $this->_percentRevLost;
        $this->percentRepLost = $this->_percentRepLost;
        $this->percentIncreasedSuccess = $this->_percentIncreasedSuccess;
        $this->tags        = $this->_tags;
    }

    public function onPreAttack($attack){
        //Handle changes to attack success rate here
        if ($this->percentIncreasedSuccess != 0){
            $attack->changeSuccessChance($this->percentIncreasedSuccess/100);
        }
    }
    
    public function onAttackComplete($attack){
        $blueteam = Team::find($attack->blueteam);
        if ($this->percentRevLost != 0) {
            $revLost = $blueteam->balance * $this->percentRevLost * 0.01 * -1;
            $blueteam->changeBalance($revLost);
        }
        if ($this->percentRepLost != 0) {
            $repLost = $blueteam->reputation * $this->percentRepLost * 0.01 * -1;
            $blueteam->changeReputation($repLost);
        }
    }

    protected function createBonus($attack){
        $bonus = new Bonus;
        $bonus->payload_name = $this->_class_name;
        $bonus->team_id = $attack->redteam;
        $bonus->target_id  = $attack->blueteam;
        $bonus->attack_id = $attack->id;
        return $bonus;
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

    public static function get($className){
        $payloads = Payload::getAll();
        foreach($payloads as $payload){
            if($payload->class_name == $className){
                return $payload;
            }
        }
        throw new AssetNotFoundException();
    }
    
}
