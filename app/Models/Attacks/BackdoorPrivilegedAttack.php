<?php

namespace App\Models\Attacks;

use App\Models\Attack;
use App\Models\Team;

class BackdoorPrivilegedAttack extends Attack {

    public $_name                   = "Backdoor (Privileged Access)";
    public $_class_name             = "BackdoorPrivileged";
    public $_tags                   = ['Internal'];
    public $_prereqs                = ['BasicAccess','PrivilegedAccess'];
    public $_initial_success_chance = 0.6;
    public $_initial_detection_chance = 0.4;
    public $_initial_energy_cost    = 150;
    public $_help_text              = "Use existing access tokens to secure more, ensuring you will maintain access to the company.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
        $redteam = Team::find($this->redteam);
        $blueteam = Team::find($this->blueteam);
        $tokens = $redteam->getTokens();
        if( !$this->success){
            if(!$this->detected){
                $this->detected = true;
                Attack::updateAttack($this);
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
        foreach($tokens as $token){
            if($token->info ==  $blueteam->name &&  $token->level == 3)
                $lowEnergy = true;
        }
        if(!$lowEnergy) {
            $this->energy_cost = (2 * $this->energy_cost);
            Attack::updateAttack($this);
            if ( $redteam->getEnergy() < $this->energy_cost ) {
                $this->possible = false;
                $this->detection_level = 0;
                $this->errormsg = "Not enough energy available.";
            }
        }
        return $this;
    }
}
