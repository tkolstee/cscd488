<?php

namespace App\Models\Attacks;

use App\Models\Attack;
use App\Models\Team;

class BackdoorPwnedAttack extends Attack {

    public $_name                   = "Backdoor (Pwned Access)";
    public $_class_name             = "BackdoorPwned";
    public $_tags                   = ['Internal','PwnedAccess'];
    public $_prereqs                = [];
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 2;
    public $_initial_energy_cost    = 400;

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
                    if($token->info == $blueteam->name){
                        $token->usedToken();
                    }
                }
            }
        }else{
            $redteam->addToken($blueteam->name, 3);
        }
    }

    function onPreAttack() {
        parent::onPreAttack();
        $redteam = Team::find($this->redteam);
        $blueteam = Team::find($this->blueteam);
        $tokens = $redteam->getTokens();
        $lowEnergy = false;
        $token1 = false;
        $token2 = false;
        foreach($tokens as $token){
            if($token->info ==  $blueteam->name &&  $token->level == 1)
                $token1 = true;
            else if($token->info ==  $blueteam->name &&  $token->level == 2)
                $token2 = true;
        }
        if(!$token1 || !$token2){
            $this->possible = false;
            $this->detected = false;
            $this->errormsg = "Missing a lower level access token.";
        }
        Attack::updateAttack($this);
        return $this;
    }
}
