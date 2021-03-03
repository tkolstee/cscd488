<?php

namespace App\Models\Attacks;

use App\Models\Attack;
use App\Models\Team;

class BackdoorPwnedAttack extends Attack {

    public $_name                   = "Backdoor (Pwned Access)";
    public $_class_name             = "BackdoorPwned";
    public $_tags                   = ['Internal'];
    public $_prereqs                = ['BasicAccess','PwnedAccess','PrivilegedAccess'];
    public $_initial_success_chance = 0.6;
    public $_initial_detection_chance = 0.4;
    public $_initial_energy_cost    = 400;
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
        return $this;
    }
}
