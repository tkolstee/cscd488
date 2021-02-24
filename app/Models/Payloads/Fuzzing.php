<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Team;

class Fuzzing extends Payload 
{

    public $_name = "Fuzzing";
    public $_class_name = "Fuzzing";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);

        $rand = rand(1,100);
        if ($rand <= 50) { //50% Confusion payload executes
            $payload = new Confusion;
            $payload->onAttackComplete($attack);
        }
        else { //50% get priv access token
            $redteam = Team::find($attack->redteam);
            $blueteam = Team::find($attack->blueteam);
            $redteam->addToken($blueteam->name, 2);
        }
    }
}
