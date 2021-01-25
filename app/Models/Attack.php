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
    protected $fillable = [ 'name', 'class_name', 'energy_cost', 'difficulty','detection_risk','success','detection_level','blueteam','redteam'];
    protected $casts = [ 'tags' => 'array', 'prereqs' => 'array', ]; // casts "json" database column to array and back

    public $_name    = "Abstract class - do not use";
    public $_class_name = "";
    public $_tags    = [];
    public $_prereqs = [];
    public $_initial_difficulty = 3;
    public $_initial_detection_risk = 3;
    public $_initial_detection = 0;
    public $_initial_energy_cost = 100;
    public $_initial_blue_loss = 0;
    public $_initial_red_gain = 0;
    public $_initial_reputation_loss = 0;
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
        $this->detection_level = $this->initial_detection;
        $this->notified       = null;
        $this->isNews         = null;
        $this->energy_cost    = $this->_initial_energy_cost;
        $this->blue_loss      = $this->_initial_blue_loss;
        $this->red_gain       = $this->_initial_red_gain;
        $this->reputation_loss= $this->_initial_reputation_loss;
    }

    function onAttackComplete() { 
        $blueteam = Team::find($this->blueteam);
        $redteam  = Team::find($this->redteam);

        if ( $this->success ) {
            $blueteam->changeBalance($this->blue_loss);
            $blueteam->changeReputation($this->reputation_loss);
            $redteam->changeBalance($this->red_gain);
        }
        if ( $this->detection_level > 0 ) {
            if( in_array("Internal", $this->tags)){
                $tokens = $redteam->getTokens();
                foreach($tokens as $token){
                    if($token->info == $blueteam->name && $token->level == 1){
                        $token->usedToken();
                    }
                }
            }
        }
        $redteam->useEnergy($this->energy_cost);
    }

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
            $att->id = $attack->id;
            $att->name = $attack->name;
            $att->class_name = $attack->class_name;
            $att->energy_cost = $attack->energy_cost;
            $att->tags = $attack->tags;
            $att->prereqs = $attack->prereqs;
            $att->difficulty = $attack->difficulty;
            $att->detection_risk = $attack->detection_risk;
            $att->success = $attack->success;
            $att->detection_level = $attack->detection_level;
            $att->notified = $attack->notified;
            $att->isNews = $attack->isNews;
            $att->possible = $attack->possible;
            $att->blueteam = $attack->blueteam;
            $att->redteam = $attack->redteam;
            $att->errormsg = $attack->errormsg;
            $att->blue_loss = $attack->blue_loss;
            $att->red_gain = $attack->red_gain;
            $att->reputation_loss = $attack->reputation_loss;
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
            $attack = Attack::store($attack);
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
        $att->detection_level = $attack->detection_level;
        $att->notified = $attack->notified;
        $att->isNews = $attack->isNews;
        $att->energy_cost = $attack->energy_cost;
        $att->possible = $attack->possible;
        $att->blueteam = $attack->blueteam;
        $att->redteam = $attack->redteam;
        $att->errormsg = $attack->errormsg;
        $att->blue_loss = $attack->blue_loss;
        $att->red_gain = $attack->red_gain;
        $att->reputation_loss = $attack->reputation_loss;
        $att->save();
        $attack->id = $att->id;
        return $attack;
    }

    public static function updateAttack($attack){
        $attacks = Attack::all()->where('class_name','=',$attack->class_name)->
            where('redteam','=',$attack->redteam)->where('blueteam','=',$attack->blueteam);
        $att = Attack::find($attack->id);
        if($att == null) throw new AttackNotFoundException;
        $att->name = $attack->name;
        $att->class_name = $attack->class_name;
        $att->tags = $attack->tags;
        $att->prereqs = $attack->prereqs;
        $att->difficulty = $attack->difficulty;
        $att->detection_risk = $attack->detection_risk;
        $att->success = $attack->success;
        $att->detection_level = $attack->detection_level;
        $att->notified = $attack->notified;
        $att->isNews = $attack->isNews;
        $att->energy_cost = $attack->energy_cost;
        $att->possible = $attack->possible;
        $att->blueteam = $attack->blueteam;
        $att->redteam = $attack->redteam;
        $att->errormsg = $attack->errormsg;
        $att->blue_loss = $attack->blue_loss;
        $att->red_gain = $attack->red_gain;
        $att->reputation_loss = $attack->reputation_loss;
        $att->update();
        $attack->id = $att->id;
        return $attack;
    }

    public static function getRedPreviousAttacks($redId) {
        return Attack::all()->where('redteam', '=', $redId);
    }

    public static function getBluePreviousAttacks($blueId) {
        return Attack::all()->where('blueteam', '=', $blueId)->where('detection_level', '>', 0);
    }

    public static function getNews() {
        return Attack::all()->where('isNews', '=', true);
    }

    public static function getUnreadDetectedAttacks($blueID) {
        return Attack::all()->where('detection_level', '>', 0)->where('notified', '=', false)->where('blueteam', '=', $blueID);
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
            $this->detection_level = 0;
        }
        else {
            $blueteam = Team::find($this->blueteam);
            if ($blueteam->hasAnalyst()) {
                $this->detection_level = 2;
            }
            else {
                $this->detection_level = 1;
            }
            $this->notified = false;
        }
        Attack::updateAttack($this);
    }

    public function setNews($newsIn) {
        $this->isNews = $newsIn;
        Attack::updateAttack($this);
    }

    public function analyze() {
        $blue = Team::find($this->blueteam);
        if ($blue->balance < 500) {
            return false;
        }
        $blue->changeBalance(-500);
        $this->detection_level = 2;
        Attack::updateAttack($this);
        return true;
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

    public function getName(){ //Restrict information given based on detection level
        if ($this->detection_level >= 2) {
            return $this->name;
        }
        return "?";
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
            $this->detection_level = 0;
            $this->errormsg = "Unsatisfied prereqs for this attack";
        }
        if ( $redteam->getEnergy() < $this->energy_cost ) {
            $this->possible = false;
            $this->detection_level = 0;
            $this->errormsg = "Not enough energy available.";
        }
        $this->checkTokens();
        Attack::updateAttack($this);
        return $this;
    }

    public function checkTokens(){
        if ( in_array("Internal", $this->tags) ){
            $redteam = Team::find($this->redteam);
            $blueteam = Team::find($this->blueteam);
            $tokenLevel = 1;
            if(in_array("PrivilegedAccess", $this->tags)) $tokenLevel = 2;
            if(in_array("PwnedAccess", $this->tags)) $tokenLevel = 3;
            $tokenOwned = false;
            $tokens = $redteam->getTokens();
            $tokensRequired = $this->tokensRequired();
            foreach( $tokens as $token){
                if( $token->info == $blueteam->name && $token->level == $tokenLevel && $token->quantity >= $tokensRequired){
                    $tokenOwned = true;
                }
            }
            if( !$tokenOwned ){
                $this->possible = false;
                $this->detection_level = 0;
                $this->errormsg = "No access token.";
            }
        }
    }

    public function tokensRequired(){
        $blueteam = Team::find($this->blueteam);
        $invs = $blueteam->inventories();
        $tokensRequired = 1;
        foreach($invs as $inv){
            $asset = Asset::get($inv->asset_name);
            if(in_array("AddToken", $asset->tags))
                $tokensRequired++;
        }
        return $tokensRequired;
    }

}