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
    protected $fillable = [ 'team_id', 'target_id','payload_name','percentRevDeducted', 'percentRepDeducted', 'percentDiffDeducted', 'percentDetDeducted', 'percentAnalDeducted'];
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
       $this->percentRevToRemove = $this->_percent_rev_remove;
    }

    public static function createBonus($team_id, $tags){
        $bonus = new Bonus();
        $bonus->team_id = $team_id;
        $bonus->tags = $tags;
        $bonus->save();
        return $bonus;
    }

    public function onTurnChange(){
        if(!in_array("UntilAnalyzed", $this->tags)){
            if(in_array("RevenueDeduction", $this->tags)){
                $this->percentRevDeducted -= 5;
            }
            if(in_array("ReputationDeduction", $this->tags)){
                $this->percentRepDeducted -= 5;
            }
            if(in_array("DetectionDeduction", $this->tags)){
                $this->percentDetDeducted -= 5;
            }
            if(in_array("AnalysisDeduction", $this->tags)){
                $this->percentAnalDeducted -= 5;
            }
            if(in_array("DifficultyDeduction", $this->tags)){
                $this->percentDiffDeducted -= 5;
            }
        }
        if(in_array("RevenueSteal", $this->tags)){
            $blueteam = Team::find($this->target_id);
            $redteam = Team::find($this->team_id);
            $revGain = $blueteam->getPerTurnRevenue();
            $amount = $revGain * 0.1;
            $blueteam->balance -= $amount;
            $redteam->balance += $amount;
            $blueteam->update();
            $redteam->update();
        }
        if(in_array("OneTurnOnly", $this->tags)){
            $this->destroy($this->id);
            return;
        }
        if(in_array("AddTokens", $this->tags)){
            $redteam = Team::find($this->team_id);
            $blueteam = Team::find($this->target_id);
            $tokenQty = $redteam->getTokenQuantity($blueteam->name, 1);
            if ($tokenQty < 5){
                $redteam->addToken($blueteam->name, 1);
            }
        }
        $this->update();
        $this->checkDelete();
    }

    public function checkDelete(){
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
}
