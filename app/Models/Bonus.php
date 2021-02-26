<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 
        'team_id', 
        'target_id',
        'payload_name',
        'percentRevDeducted', 
        'percentRepDeducted', 
        'percentDiffDeducted', 
        'percentDetDeducted', 
        'percentAnalDeducted', 
        'percentRemoval',
        'removalCostFactor',
        'revenueGenerated',
        'percentRevStolen'
    ];
    protected $casts = [ 'tags' => 'array']; // casts "json" database column to array and back

    public $_tags    = [];
    public $_rev = 0;
    public $_rep = 0;
    public $_det = 0;
    public $_diff = 0;
    public $_analysis = 0;
    public $_team_id = null;
    public $_target_id = null;
    public $_payload_name = null;
    public $_percent_removal = 0;
    public $_percent_rev_remove = 0;
    public $_removalChance = 0;
    public $_removalCostFactor = 1;
    public $_attack_id = null;
    public $_revenue_generated = 0;
    public $_percent_rev_stolen = 0;

    function __construct() {
       $this->tags = $this->_tags;
       $this->team_id = $this->_team_id;
       $this->target_id = $this->_target_id;
       $this->percentRemoval = $this->_percent_removal;
       $this->percentRevDeducted = $this->_rev;
       $this->percentRepDeducted = $this->_rep;
       $this->percentDetDeducted = $this->_det;
       $this->percentDiffDeducted = $this->_diff;
       $this->percentAnalDeducted = $this->_analysis;
       $this->payload_name = $this->_payload_name;
       $this->removalChance = $this->_removalChance;
       $this->removalCostFactor = $this->_removalCostFactor;
       $this->attack_id = $this->_attack_id;
       $this->percentRevToRemove = $this->_percent_rev_remove;
       $this->revenueGenerated = $this->_revenue_generated;
       $this->percentRevStolen = $this->_percent_rev_stolen;
    }

    public static function createBonus($team_id, $tags){
        $bonus = new Bonus();
        $bonus->team_id = $team_id;
        $bonus->tags = $tags;
        $bonus->save();
        return $bonus;
    }

    public function onTurnChange(){
        $redteam = Team::find($this->team_id);
        $blueteam = Team::find($this->target_id);
        if(!$this->hasTag("UntilAnalyzed")){
            if($this->hasTag("RevenueDeduction")){
                $this->percentRevDeducted -= 5;
            }
            if($this->hasTag("ReputationDeduction")){
                $this->percentRepDeducted -= 5;
            }
            if($this->hasTag("DetectionDeduction")){
                $this->percentDetDeducted -= 5;
            }
            if($this->hasTag("AnalysisDeduction")){
                $this->percentAnalDeducted -= 5;
            }
            if($this->hasTag("DifficultyDeduction")){
                $this->percentDiffDeducted -= 5;
            }
        }
        if($this->hasTag("RevenueSteal")){
            $revGain = $blueteam->getPerTurnRevenue();
            $amount = $revGain * ($this->percentRevStolen * 0.01);
            $blueteam->changeBalance(-1* $amount);
            $redteam->changeBalance($amount);
        }
        if($this->hasTag("RevenueGeneration")){
            $redteam->changeBalance($this->revenueGenerated);
        }
        if($this->hasTag("AddTokens")){
            $tokenQty = $redteam->getTokenQuantity($blueteam->name, 1);
            if ($tokenQty < 5){
                $redteam->addToken($blueteam->name, 1);
            }
        }
        if($this->hasTag("OneTurnOnly")){
            $this->destroy($this->id);
            return;
        }
        if($this->hasTag("ChanceToRemove")){
            $rand = rand(1, 100);
            if ($rand <= $this->removalChance) {
                $this->destroy($this->id);
                return;
            }
        }
        $this->update();
        $this->checkDelete();
    }

    public function hasTag($tag){
        return in_array($tag, $this->tags);
    }

    public function getPayloadName(){
        $attack = Attack::find($this->attack_id);
        if($attack->detection_level > 1){
            return $this->payload_name;
        }
        return "?";
    }

    public function getTeamName(){
        $attack = Attack::find($this->attack_id);
        if($attack->detection_level > 2){
            return Team::find($this->team_id)->name;
        }
        return "?";
    }

    public function getTeamDescription(){
        $desc = "";
        if($this->hasTag("RevenueSteal")) $desc = $desc . 
            "Steals " . $this->percentRevStolen . "% of target's revenue made each turn. ";
        if($this->hasTag("RevenueGeneration")) $desc = $desc .
            "Your team generates $" . $this->revenueGenerated . " each turn. ";
        if($this->hasTag("RevenueDeduction"))  $desc = $desc .  
            "Target loses " . $this->percentRevDeducted . "% of revenue made this turn. ";
        if($this->hasTag("ReputationDeduction"))  $desc = $desc . 
            " Target loses " . $this->percentRepDeducted. "% of reputation made this turn. " ;
        if($this->hasTag("DetectionDeduction"))  $desc = $desc .  
            "You have " . $this->percentDetDeducted. "% less chance of being detected by target. ";
        if($this->hasTag("AnalysisDeduction"))  $desc = $desc .  
            "You have " . $this->percentAnalDeducted. "% less chance of being analyzed by target. ";
        if($this->hasTag("DifficultyDeduction"))  $desc = $desc .  
            "It is " . $this->percentDiffDeducted. "% easier to be successful attacking the target. ";
        if($this->hasTag("ChanceToRemove"))  $desc = $desc . 
            "Has a " . $this->removalChance . "% chance to be automatically removed each turn. ";
        if($this->hasTag("PayToRemove"))  $desc = $desc . 
            "Target can pay you ". $this->removalCostFactor . " times their per turn revenue to remove this bonus. ";
        elseif($this->hasTag("UntilAnalyzed"))  $desc = $desc .  
            "Bonus lasts until the target analyzes the attack.";
        elseif($this->hasTag("OneTurnOnly"))  $desc = $desc . 
            "Bonus only lasts until next turn. ";
        else  $desc = $desc .  "Decrements by 5% each turn.";
        return $desc;
    }

    public function getTargetDescription(){
        $desc = "";
        if($this->hasTag("RevenueSteal")) $desc = $desc .  
            "Attacker steals " . $this->percentRevStolen . "% of your revenue made each turn. ";
        if($this->hasTag("RevenueGeneration")) $desc = $desc .
            "The attacking team generates $" . $this->revenueGenerated . " each turn. This is not stolen. ";
        if($this->hasTag("RevenueDeduction"))  $desc = $desc .  
            "You lose " . $this->percentRevDeducted . "% of revenue made this turn. ";
        if($this->hasTag("ReputationDeduction"))  $desc = $desc . 
            "You lose " . $this->percentRepDeducted . "% of reputation made this turn. " ;
        if($this->hasTag("DetectionDeduction"))  $desc = $desc .  
            "You have " . $this->percentDetDeducted . "% less chance of detecting this attacker. ";
        if($this->hasTag("AnalysisDeduction"))  $desc = $desc .  
            "You have " . $this->percentAnalDeducted . "% less chance of analyzing this attacker. ";
        if($this->hasTag("DifficultyDeduction"))  $desc = $desc .  
            "It is " . $this->percentDiffDeducted . "% easier for the attacker to be successful against you. ";
        if($this->hasTag("ChanceToRemove"))  $desc = $desc . 
            "You have a " . $this->removalChance . "% chance to automatically remove this each turn. ";
        if($this->hasTag("PayToRemove"))  $desc = $desc . 
            "You can pay the attacker " . $this->removalCostFactor . " times your per turn revenue to remove this bonus. ";
        elseif($this->hasTag("UntilAnalyzed"))  $desc = $desc .  
            "Bonus lasts until you analyze the attack.";
        elseif($this->hasTag("OneTurnOnly"))  $desc = $desc . 
            "Bonus only lasts until next turn. ";
        else  $desc = $desc .  "Decrements by 5% each turn.";
        return $desc;
    }

    public function checkDelete(){
        if ($this->hasTag("ChanceToRemove")){
            return;
        }
        if ($this->hasTag("UntilAnalyzed")){
            return;
        }
        if($this->percentDiffDeducted > 0){
            return;
        }
        if($this->percentAnalDeducted > 0){
            return;
        }
        if($this->percentDetDeducted > 0){
            return;
        }
        if($this->percentRepDeducted > 0){
            return;
        }
        if($this->percentRevDeducted > 0){
            return;
        }
        $this->destroy($this->id);
    }

    public function payToRemove(){
        if (!$this->hasTag('PayToRemove')){
            return false;
        }
        $blueteam = Team::find($this->target_id);
        $redteam = Team::find($this->team_id);
        $cost = $blueteam->getPerTurnRevenue() * $this->removalCostFactor;
        if ($cost > $blueteam->balance){
            return false;
        }

        if ($cost < 0) { $cost = 0;}

        $blueteam->changeBalance($cost *-1);
        $redteam->changeBalance($cost);
        $this->destroy($this->id);
        return true;
    }
}
