<?php

namespace App\Models\Attacks;

use App\Models\Attack;
use App\Models\Team;

class BackdoorBasicAttack extends Attack {

    public $_name                   = "Backdoor (Basic Access)";
    public $_class_name             = "BackdoorBasic";
    public $_tags                   = ['Internal'];
    public $_prereqs                = [];
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 2;
    public $_initial_energy_cost    = 100;
    public $_initial_blue_loss      = 0;
    public $_initial_red_gain       = 0;
    public $_initial_reputation_loss= 0;
    public $possible                = true;


    function onAttackComplete() {
        parent::onAttackComplete();
        $redteam = Team::find($this->redteam);
        $blueteam = Team::find($this->blueteam);
        $tokens = $redteam->getTokens();
        if( !$this->success){
            if(!$this->detected){
                $this->detected = true;
                foreach($tokens as $token){
                    if($token->info == $blueteam->name && $token->level == 1){
                        $token->usedToken();
                    }
                }
            }
        }else{
            $redteam->addToken($blueteam->name, 1);
        }
    }

    function onPreAttack() {
        parent::onPreAttack();
        $redteam = Team::find($this->redteam);
        $blueteam = Team::find($this->blueteam);
        $tokens = $redteam->getTokens();
        $lowEnergy = false;
        foreach($tokens as $token){
            if($token->info ==  $blueteam->name && ($token->level == 2 || $token->level == 3))
                $lowEnergy = true;
        }
        if(!$lowEnergy) $this->energy_cost = (2 * $this->energy_cost);
        Attack::updateAttack($this);
        return $this;
    }
}