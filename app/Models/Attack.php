<?php

namespace App\Models;

use App\Exceptions\AttackNotFoundException;
use App\Exceptions\TeamNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Error;

class Attack extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name', 'class_name', 'energy_cost', 'difficulty','detection_risk','success','detected','blueteam','redteam'];
    protected $casts = [ 'tags' => 'array', 'prereqs' => 'array', ]; // casts "json" database column to array and back

    public $_name    = "Abstract class - do not use";
    public $_class_name = "";
    public $_tags    = [];
    public $_prereqs = [];
    public $_initial_difficulty = 3;
    public $_initial_detection_risk = 3;
    public $_initial_energy_cost = 100;
    public $possible = true;
    public $errormsg = "";

    function __construct() {
        $this->name           = $this->_name;
        $this->class_name     = $this->_class_name;
        $this->difficulty     = $this->_initial_difficulty;
        $this->detection_risk = $this->_initial_detection_risk;
        $this->tags           = $this->_tags;
        $this->prereqs        = $this->_prereqs;
        $this->success        = null;
        $this->detected       = null;
        $this->notified       = null;
        $this->energy_cost    = $this->_initial_energy_cost;
    }

    function onAttackComplete() { }

    public static function getAll(){
        $dir = opendir(dirname(__FILE__)."/Attacks");
        while(($attack = readdir($dir)) !== false){
            if($attack != "." && $attack != ".."){
                $length = strlen($attack);
                $attack = substr($attack, 0, $length - 4);
                $class = "\\App\\Models\\Attacks\\" . $attack;
                $attacks[] = new $class();
            }
            
        }
        return $attacks;
    }

    public static function get($name, $red, $blue){
        $attack = Attack::all()->where('class_name','=',$name)->where('redteam','=',$red)->where('blueteam','=',$blue)->where('success','=',null)->first();
        try {
            $class = "\\App\\Models\\Attacks\\" . $attack->class_name . "Attack";
            $att = new $class();
            $att->name = $attack->name;
            $att->class_name = $attack->class_name;
            $att->energy_cost = $attack->energy_cost;
            $att->tags = $attack->tags;
            $att->prereqs = $attack->prereqs;
            $att->difficulty = $attack->difficulty;
            $att->detection_risk = $attack->detection_risk;
            $att->success = $attack->success;
            $att->detected = $attack->detected;
            $att->notified = $attack->notified;
            $att->possible = $attack->possible;
            $att->blueteam = $attack->blueteam;
            $att->redteam = $attack->redteam;
            $att->errormsg = $attack->errormsg;
        }
        catch (Error $e) {
            throw new AttackNotFoundException();
        }
        return $att;
    }

    public static function create($attackName, $redID, $blueID){
        if (Team::find($redID) == null || Team::find($blueID) == null) {
            throw new TeamNotFoundException();
        }
        try {
            $class = "\\App\\Models\\Attacks\\" . $attackName . "Attack";
            $attack = new $class();
            $attack->blueteam = $blueID;
            $attack->redteam = $redID;
            Attack::store($attack);
            return $attack;
        }
        catch(Error $e) {
            throw new AttackNotFoundException();
        }
    }

    public static function store($attack){
        $att = new Attack();
        $att->name = $attack->name;
        $att->class_name = $attack->class_name;
        $att->tags = $attack->tags;
        $att->prereqs = $attack->prereqs;
        $att->difficulty = $attack->difficulty;
        $att->detection_risk = $attack->detection_risk;
        $att->success = $attack->success;
        $att->detected = $attack->detected;
        $att->energy_cost = $attack->energy_cost;
        $att->possible = $attack->possible;
        $att->blueteam = $attack->blueteam;
        $att->redteam = $attack->redteam;
        $att->errormsg = $attack->errormsg;
        $att->save();
        return $attack;
    }

    public static function updateAttack($attack){
        $attacks = Attack::all()->where('class_name','=',$attack->class_name)->
            where('redteam','=',$attack->redteam)->where('blueteam','=',$attack->blueteam);
        foreach($attacks as $atk){
            if($atk->success == null || $atk->detected == null || $atk->notified == false){
                $att = $atk;
            }
        }
        if($att == null) throw new AttackNotFoundException;
        $att->name = $attack->name;
        $att->class_name = $attack->class_name;
        $att->tags = $attack->tags;
        $att->prereqs = $attack->prereqs;
        $att->difficulty = $attack->difficulty;
        $att->detection_risk = $attack->detection_risk;
        $att->success = $attack->success;
        $att->detected = $attack->detected;
        $att->notified = $attack->notified;
        $att->energy_cost = $attack->energy_cost;
        $att->possible = $attack->possible;
        $att->blueteam = $attack->blueteam;
        $att->redteam = $attack->redteam;
        $att->errormsg = $attack->errormsg;
        $att->update();
        return $attack;
    }

    public static function getPreviousAttacks($redId) {
        return Attack::all()->where('redteam', '=', $redId);
    }

    public static function getDetectedAttacks() {
        return Attack::all()->where('detected', '=', true);
    }

    public static function getUnreadDetectedAttacks($blueID) {
        return Attack::all()->where('detected', '=', true)->where('notified', '=', false)->where('blueteam', '=', $blueID);
    }

    public function setNotified($notifiedIn) {
        $this->notified = $notifiedIn;
        Attack::updateAttack($this);
    }

    public function setSuccess($successIn) {
        $this->success = $successIn;
        Attack::updateAttack($this);
        $this->calculateDetected();
    }

    public function calculateDetected() {
        $rand = rand(1, 4);
        if ($rand >= $this->detection_risk) {
            $this->detected = false;
        }
        else {
            $this->detected = true;
            $this->notified = false;
        }
        Attack::updateAttack($this);
    }

    public function changeDifficulty($val){
        $this->difficulty += $val;
        if($this->difficulty > 5) $this->difficulty = 5;
        if($this->difficulty < 1) $this->difficulty = 1;
        Attack::updateAttack($this);
    }

    public function changeDetectionRisk($val){
        $this->detection_risk += $val;
        if($this->detection_risk > 5) $this->detection_risk = 5;
        if($this->detection_risk < 1) $this->detection_risk = 1;
        Attack::updateAttack($this);
    }

    public function onPreAttack() {
        $blueteam = Team::find($this->blueteam);
        $redteam  = Team::find($this->redteam);

        // Get all assets in both attacker's and target's inventories
        $blueInv = $blueteam->inventories();
        $redInv = $redteam->inventories();

        // Collect all tags and names of these assets to match against prerequisites for this attack
        $have = [];

        // Each asset has an opportunity to modify the attack object
        //
        foreach ($blueInv as $inv) {
            $asset = Asset::get($inv->asset_name);
            $asset->onPreAttack($this);
            $have[] = $asset->class_name;
            foreach ( $asset->tags as $tag ) { $have[] = $tag; }
        }
        foreach ($redInv as $inv) {
            $asset = Asset::get($inv->asset_name);
            $asset->onPreAttack($this);
            $have[] = $asset->class_name;
            foreach ( $asset->tags as $tag ) { $have[] = $tag; }
        }
        $unmet_prereqs = array_diff($this->prereqs, $have);
        if ( count($unmet_prereqs) > 0 ) {
            $this->possible = false;
            $this->detected = false;
            $this->errormsg = "Unsatisfied prereqs for this attack";
        }
        if ( $redteam->getEnergy() < $this->energy_cost ) {
            $this->possible = false;
            $this->detected = false;
            $this->errormsg = "Not enough energy available.";
        }
        Attack::updateAttack($this);
        return $this;
    }

}