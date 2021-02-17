<?php

namespace App\Models\Attacks;

use App\Models\Attack;
use App\Models\Team;

class BackdoorBasicAttack extends Attack {

    public $_name                   = "Backdoor (Basic Access)";
    public $_class_name             = "BackdoorBasic";
    public $_tags                   = ['Internal'];
    public $_prereqs                = [];
    public $_initial_success_chance  = 2;
    public $_initial_detection_chance = 2;
    public $_initial_energy_cost    = 100;
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
        if ( $redteam->getEnergy() < $this->energy_cost ) {
            $this->possible = false;
            $this->detection_level = 0;
            $this->errormsg = "Not enough energy available.";
        }
        return $this;
    }
}
