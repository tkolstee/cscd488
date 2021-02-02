<?php

namespace App\Models\Attacks;

use App\Models\Attack;
use App\Models\Team;

class BackdoorPrivilegedAttack extends Attack {

    public $_name                   = "Backdoor (Privileged Access)";
    public $_class_name             = "BackdoorPrivileged";
    public $_tags                   = ['Internal','PrivilegedAccess'];
    public $_prereqs                = [];
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 2;
    public $_initial_energy_cost    = 150;


    function onAttackComplete() {
        parent::onAttackComplete();
        $redteam = Team::find($this->redteam);
        $blueteam = Team::find($this->blueteam);
        $tokens = $redteam->getTokens();
        if( !$this->success){
            if(!$this->detected){
                $this->detected = true;
                foreach($tokens as $token){
                    if($token->info == $blueteam->name && ($token->level == 1 || $token->level == 2)){
                        $token->usedToken();
                    }
                }
            }
        }else{
            $redteam->addToken($blueteam->name, 2);
        }
    }

    function onPreAttack() {
        parent::onPreAttack();
        $redteam = Team::find($this->redteam);
        $blueteam = Team::find($this->blueteam);
        $tokens = $redteam->getTokens();
        $lowEnergy = false;
        $lowerTokenOwned = false;
        foreach($tokens as $token){
            if($token->info ==  $blueteam->name &&  $token->level == 3)
                $lowEnergy = true;
            else if($token->info ==  $blueteam->name &&  $token->level == 1)
                $lowerTokenOwned = true;
        }
        if(!$lowerTokenOwned){
            $this->possible = false;
            $this->detected = false;
            $this->errormsg = "No basic access token.";
        }
        if(!$lowEnergy) $this->energy_cost = (2 * $this->energy_cost);
        Attack::updateAttack($this);
        return $this;
    }
}
