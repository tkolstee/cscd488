<?php

namespace App\Models;

use App\Exceptions\AttackNotFoundException;
use App\Exceptions\TeamNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payload;
use Error;

class Attack extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name', 'class_name', 'energy_cost', 'difficulty','detection_risk', 'calculated_detection_risk', 'calculated_difficulty','success','detection_level','blueteam','redteam'];
    protected $casts = [ 'tags' => 'array', 'prereqs' => 'array', 'payloads' => 'array']; // casts "json" database column to array and back

    public $_name    = "Abstract class - do not use";
    public $_class_name = "";
    public $_tags    = [];
    public $_prereqs = [];
    public $_payload_tag = null;
    public $_payload_choice = null;
    public $_initial_difficulty = 3;
    public $_initial_detection_risk = 3;
    public $_initial_detection = 0;
    public $_initial_energy_cost = 100;
    public $_possible = true;
    public $errormsg = "";
    public $_initial_analysis_risk = null;
    public $_initial_attribution_risk = null;
    public $_help_text = null;

    function __construct() {
        $this->name           = $this->_name;
        $this->class_name     = $this->_class_name;
        $this->difficulty     = $this->_initial_difficulty;
        $this->detection_risk = $this->_initial_detection_risk;
        $this->analysis_risk  = $this->_initial_analysis_risk;
        $this->attribution_risk = $this->_initial_attribution_risk;
        $this->calculated_analysis_risk = $this->_initial_analysis_risk;
        $this->calculated_attribution_risk = $this->_initial_attribution_risk;
        $this->calculated_difficulty = $this->_initial_difficulty;
        $this->calculated_detection_risk = $this->_initial_detection_risk;
        $this->tags           = $this->_tags;
        $this->prereqs        = $this->_prereqs;
        $this->payload_tag    = $this->_payload_tag;
        $this->payload_choice = $this->_payload_choice;
        $this->success        = null;
        $this->detection_level = $this->initial_detection;
        $this->notified       = null;
        $this->isNews         = null;
        $this->energy_cost    = $this->_initial_energy_cost;
        $this->possible = $this->_possible;
        $this->help_text = $this->_help_text;
    }

    function onAttackComplete() { 
        $blueteam = Team::find($this->blueteam);
        $redteam  = Team::find($this->redteam);

        if ( $this->success && $this->payload_choice != null) {
            $payload = Payload::get($this->payload_choice);
            $payload->onAttackComplete($this);
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
        $redteam->changeBalance($this->energy_cost);
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

    public static function getLearnableAttacks(){
        $dir = opendir(dirname(__FILE__)."/Attacks");
        while(($attack = readdir($dir)) !== false){
            if($attack != "." && $attack != ".."){
                $length = strlen($attack);
                $attack = substr($attack, 0, $length - 4);
                $class = "\\App\\Models\\Attacks\\" . $attack;
                $attack = new $class();
                if ($attack->learn_page == true) {
                    $attacks[] = $attack;
                }
            }
        }
        return $attacks;
    }

    public static function get($name, $red, $blue){
        $attack = Attack::all()->where('class_name','=',$name)->where('redteam','=',$red)->where('blueteam','=',$blue)->where('success','=',null)->first();
        try {
            $class = "\\App\\Models\\Attacks\\" . $attack->class_name . "Attack";
            $att = new $class();
            $att->copy($attack);
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
        $att->copy($attack);
        $att->save();
        $attack->id = $att->id;
        return $attack;
    }

    public static function updateAttack($attack){
        $attacks = Attack::all()->where('class_name','=',$attack->class_name)->
            where('redteam','=',$attack->redteam)->where('blueteam','=',$attack->blueteam);
        $att = Attack::find($attack->id);
        if($att == null) throw new AttackNotFoundException;
        $att->copy($attack);
        $att->update();
        $attack->id = $att->id;
        return $attack;
    }

    private function copy($attack) {
        $this->id = $attack->id;
        $this->name = $attack->name;
        $this->class_name = $attack->class_name;
        $this->energy_cost = $attack->energy_cost;
        $this->tags = $attack->tags;
        $this->prereqs = $attack->prereqs;
        $this->payload_tag = $attack->payload_tag;
        $this->payload_choice = $attack->payload_choice;
        $this->difficulty = $attack->difficulty;
        $this->detection_risk = $attack->detection_risk;
        $this->analysis_risk = $attack->analysis_risk;
        $this->attribution_risk = $attack->attribution_risk;
        $this->calculated_detection_risk = $attack->calculated_detection_risk;
        $this->calculated_difficulty = $attack->calculated_difficulty;
        $this->calculated_analysis_risk = $attack->calculated_analysis_risk;
        $this->calculated_attribution_risk = $attack->calculated_attribution_risk;
        $this->success = $attack->success;
        $this->detection_level = $attack->detection_level;
        $this->notified = $attack->notified;
        $this->isNews = $attack->isNews;
        $this->possible = $attack->possible;
        $this->blueteam = $attack->blueteam;
        $this->redteam = $attack->redteam;
        $this->errormsg = $attack->errormsg;
        $this->help_text = $attack->help_text;
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

    public function checkAnalysisBonus(){
        if($this->detection_level < 2){
            return;
        }
        $bonuses = Bonus::all()->where('attack_id','=',$this->id);
        foreach($bonuses as $bonus){
            if(in_array("UntilAnalyzed",$bonus->tags)){
                Bonus::destroy($bonus->id);
            }
        }
    }

    public function calculateDetected() {
        $rand = rand(0, 500)/100;
        if ($rand > $this->calculated_detection_risk) {
            $this->detection_level = 0;
        }
        else {
            $this->detection_level = 1;
            $blueteam = Team::find($this->blueteam);
            $rand = rand(0, 500)/100;
            if ($rand < $this->calculated_analysis_risk){
                $this->detection_level = 2;
                $this->checkAnalysisBonus();
                $rand = rand(0, 500)/100;
                if($rand < $this->calculated_attribution_risk){
                    $this->detection_level = 3;
                }
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
        $this->checkAnalysisBonus();
        return true;
    }

    public function getBonuses(){
        $bonuses = Bonus::all()->where('team_id', '=', $this->redteam)->where('target_id','=',$this->blueteam);
        return $bonuses;
    }
    
    public function changeDifficulty($val){
        $this->calculated_difficulty += $val * $this->difficulty;
        if($this->calculated_difficulty > 5) $this->calculated_difficulty = 5;
        if($this->calculated_difficulty < 1) $this->calculated_difficulty = 1;
        Attack::updateAttack($this);
    }

    public function changeDetectionRisk($val){
        $this->calculated_detection_risk += $val * $this->detection_risk;
        if($this->calculated_detection_risk > 5) $this->calculated_detection_risk = 5;
        if($this->calculated_detection_risk < 0) $this->calculated_detection_risk = 0;
        Attack::updateAttack($this);
    }

    public function changeAnalysisRisk($val){
        $this->calculated_analysis_risk += $val * $this->analysis_risk;
        if($this->calculated_analysis_risk > 5) $this->calculated_analysis_risk = 5;
        if($this->calculated_analysis_risk < 0) $this->calculated_analysis_risk = 0;
        Attack::updateAttack($this);
    }

    public function changeAttributionRisk($val){
        $this->calculated_detection_risk += $val * $this->detection_risk;
        if($this->calculated_detection_risk > 5) $this->calculated_detection_risk = 5;
        if($this->calculated_detection_risk < 0) $this->calculated_detection_risk = 0;
        Attack::updateAttack($this);
    }

    public function getName(){ //Restrict information given based on detection level
        if ($this->detection_level > 1) {
            return $this->name;
        }
        return "?";
    }

    public function getAttackerName(){
        if ($this->detection_level > 2) {
            return Team::find($this->redteam)->name;
        }
        return "?";
    }

    public function getPayloads(){
        if ($this->payload_tag == null){ return null; }
        return Payload::getByTag($this->payload_tag);
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
        $bonuses = $this->getBonuses();
        foreach ($bonuses as $bonus){
            if(in_array("DifficultyDeduction", $bonus->tags)){
                $this->changeDifficulty(-1* $bonus->percentDiffDeducted);
            }
            if(in_array("DetectionDeduction", $bonus->tags)){
                $this->changeDetectionRisk(-1* $bonus->percentDetDeducted);
            }
        }
        $this->calculated_difficulty = round($this->calculated_difficulty);

        if (!Game::prereqsDisabled()) {
            $unmet_prereqs = array_diff($this->prereqs, $have);
            if ( count($unmet_prereqs) > 0 ) {
                $this->possible = false;
                $this->detection_level = 0;
                $this->errormsg = "Unsatisfied prereqs for this attack";
            }
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