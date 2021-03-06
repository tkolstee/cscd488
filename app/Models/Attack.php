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
    protected $fillable = [ 'name', 'class_name', 'energy_cost', 'success_chance','detection_chance', 'calculated_detection_chance', 'calculated_success_chance','success','detection_level','blueteam','redteam'];
    protected $casts = [ 'tags' => 'array', 'prereqs' => 'array', 'payloads' => 'array']; // casts "json" database column to array and back

    public $_name    = "Abstract class - do not use";
    public $_class_name = "";
    public $_tags    = [];
    public $_prereqs = [];
    public $_payload_tag = null;
    public $_payload_choice = null;
    public $_initial_success_chance = 0.5;
    public $_initial_detection_chance = 0.5;
    public $_initial_detection = 0;
    public $_initial_energy_cost = 100;
    public $_possible = true;
    public $errormsg = "";
    public $_initial_analysis_chance = null;
    public $_initial_attribution_chance = null;
    public $_help_text = null;

    function __construct() {
        $this->name           = $this->_name;
        $this->class_name     = $this->_class_name;
        $this->success_chance = $this->_initial_success_chance;
        $this->detection_chance = $this->_initial_detection_chance;
        $this->analysis_chance  = $this->_initial_analysis_chance;
        $this->attribution_chance = $this->_initial_attribution_chance;
        $this->calculated_analysis_chance = $this->_initial_analysis_chance;
        $this->calculated_attribution_chance = $this->_initial_attribution_chance;
        $this->calculated_success_chance = $this->_initial_success_chance;
        $this->calculated_detection_chance = $this->_initial_detection_chance;
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
        $redteam  = Team::find($this->redteam);

        if ($this->success) {
            $redteam->changeBalance($this->energy_cost);
            if ($this->payload_choice != null) {
                $payload = Payload::get($this->payload_choice);
                $payload->onAttackComplete($this);
            }
        }

        if ($this->detection_level == 0) {
            $bonuses = $this->getBonuses();
            foreach ($bonuses as $bonus){
                if ($bonus->hasTag("UntilAnalyzed")){
                    $this->detection_level = 1;
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
        $this->success_chance = $attack->success_chance;
        $this->detection_chance = $attack->detection_chance;
        $this->analysis_chance = $attack->analysis_chance;
        $this->attribution_chance = $attack->attribution_chance;
        $this->calculated_detection_chance = $attack->calculated_detection_chance;
        $this->calculated_success_chance = $attack->calculated_success_chance;
        $this->calculated_analysis_chance = $attack->calculated_analysis_chance;
        $this->calculated_attribution_chance = $attack->calculated_attribution_chance;
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
            if($bonus->hasTag("UntilAnalyzed")){
                Bonus::destroy($bonus->id);
            }
        }
    }

    public function calculateDetected() {
        $rand = rand(0, 99)/100;
        if ($rand >= $this->calculated_detection_chance) {
            $this->detection_level = 0;
        }
        else {
            $this->detection_level = 1;
            $rand = rand(0, 99)/100;
            if ($rand < $this->calculated_analysis_chance){
                $this->detection_level = 2;
                $this->checkAnalysisBonus();
                $rand = rand(0, 99)/100;
                if($rand < $this->calculated_attribution_chance){
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
    
    public function changeSuccessChance($val){
        $this->calculated_success_chance = $this->calculateNewProbability($this->calculated_success_chance, $val);
        Attack::updateAttack($this);
    }

    public function changeDetectionChance($val){
        $this->calculated_detection_chance = $this->calculateNewProbability($this->calculated_detection_chance, $val);
        Attack::updateAttack($this);
    }

    public function changeAnalysisChance($val){
        $this->calculated_analysis_chance = $this->calculateNewProbability($this->calculated_analysis_chance, $val);
        Attack::updateAttack($this);
    }

    public function changeAttributionChance($val){
        $this->calculated_attribution_chance = $this->calculateNewProbability($this->calculated_attribution_chance, $val);
        Attack::updateAttack($this);
    }

    private function calculateNewProbability($initial, $val){
        $result = $initial + ($initial*$val);
        if ($result > 1) { return 1; }
        elseif ($result < 0) { return 0; }
        else { return $result; }
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

    /**
     * Converts success_chance to difficulty for minigames. 5 = impossible, 0 = always succeeds
     */
    public function getDifficulty(){
        $difficulty = round(5*(1-$this->calculated_success_chance));
        if($difficulty < 1){
            $difficulty = 1;
        }
        return $difficulty;
    }

    public function hasTag($tag){
        return in_array($tag, $this->tags);
    }

    public function hasPrereq($prereq){
        return in_array($prereq, $this->prereqs);
    }

    public function onPreAttack() {
        $blueteam = Team::find($this->blueteam);
        $redteam  = Team::find($this->redteam);

        //Check prereqs, energy cost first, and token amount, exit early if conditions not met
        if (!Game::prereqsDisabled()) {
            $redTags = $redteam->collectAssetTags($this);
            $blueTags = $blueteam->collectAssetTags($this);
            $have = array_merge($redTags,$blueTags);
            $unmet_prereqs = array_diff($this->prereqs, $have);
            if ( count($unmet_prereqs) > 0 ) {
                return $this->failPreAttack("Unsatisfied prereqs for this attack");
            }
        }
        if ( $redteam->getEnergy() < $this->energy_cost ) {
            return $this->failPreAttack("Not enough energy available.");
        }
        if (!$this->hasTokensNeeded()) {
            return $this->failPreAttack("No access token.");
        }

        // Each asset gets chance to modify attack, then apply bonuses
        $blueInv = $blueteam->inventories();
        $redInv = $redteam->inventories();
        $inventories = $blueInv->merge($redInv);
        foreach ($inventories as $inv) {
            $asset = Asset::get($inv->asset_name);
            if (isValidTargetedAsset($asset, $inv->info, $this)){
                $asset->onPreAttack($this);
            }
        }
        $bonuses = $this->getBonuses();
        foreach ($bonuses as $bonus){
            $this->applyBonus($bonus);
        }
        Attack::updateAttack($this);
        return $this;
    }

    private function failPreAttack($errormsg){
        $this->possible = false;
        $this->detection_level = 0;
        $this->errormsg = $errormsg;
        Attack::updateAttack($this);
        return $this;
    }

    private function hasTokensNeeded(){
        if ($this->hasTag('Internal')){
            $redteam = Team::find($this->redteam);
            $blueteam = Team::find($this->blueteam);
            $tokenLevel = 1;
            if($this->hasPrereq('PrivilegedAccess')) $tokenLevel = 2;
            elseif($this->hasPrereq('PwnedAccess')) $tokenLevel = 3;
            $tokens = $redteam->getTokens();
            $tokensRequired = $this->tokensRequired();
            foreach( $tokens as $token){
                if( $token->info == $blueteam->name && $token->level == $tokenLevel && $token->quantity >= $tokensRequired){
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    private function tokensRequired(){
        $blueteam = Team::find($this->blueteam);
        $invs = $blueteam->inventories();
        $tokensRequired = 1;
        foreach($invs as $inv){
            $asset = Asset::get($inv->asset_name);
            if($asset->hasTag("AddToken"))
                $tokensRequired++;
        }
        return $tokensRequired;
    }

    private function applyBonus($bonus){
        if($bonus->hasTag("DifficultyDeduction")){
            $this->changeSuccessChance(-1 * 0.01 * $bonus->percentDiffDeducted);
        }
        if($bonus->hasTag("DetectionDeduction")){
            $this->changeDetectionChance(-1 * 0.01 * $bonus->percentDetDeducted);
        }
    }
}