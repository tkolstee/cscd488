<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Team;

class StolenLaptop extends Payload 
{

    public $_name = "Stolen Laptop";
    public $_class_name = "StolenLaptop";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);

        $rand = rand(1,100);
        if ($rand <= 25) { //25% secinfo, reduces detection and difficulty 
            $bonus = parent::createBonus($attack);
            $bonus->tags = ['DetectionDeduction','DifficultyDeduction'];
            $bonus->percentDetDeducted = 20;
            $bonus->percentDiffDeducted = 20;
        }
        $rand = rand(1,100);
        if ($rand <= 50) { //50% fininfo, steal 40% per turn rev for one turn
            $bonus = parent::createBonus($attack);
            $bonus->tags = ['OneTurnOnly', 'RevenueSteal'];
            $bonus->percentRevStolen = 40;
        }
        $rand = rand(1,100);
        if ($rand <= 80) { //80% basic access
            $redteam = Team::find($attack->redteam);
            $blueteam = Team::find($attack->blueteam);
            $redteam->addToken($blueteam->name, 1);
        }
        $rand = rand(1,100);
        if ($rand <= 20) { //20% priv access
            $redteam = Team::find($attack->redteam);
            $blueteam = Team::find($attack->blueteam);
            $redteam->addToken($blueteam->name, 2);
        }
        $rand = rand(1,100);
        if ($rand <= 1) { //1% pwned access
            $redteam = Team::find($attack->redteam);
            $blueteam = Team::find($attack->blueteam);
            $redteam->addToken($blueteam->name, 3);
        }
    }
}
