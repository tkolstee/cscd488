<?php

namespace App\Models\Payloads;

use App\Models\Team;
use App\Models\Payload;

class Destruction extends Payload 
{

    public $_name = "Resource Destruction";
    public $_class_name = "Destruction";
    public $_tags = ['DBAttack','EndpointExecutable'];

    public function onAttackComplete($attack){
        $bonus = parent::onAttackComplete($attack);
        //Blueteam loses 10% of revenue immediately and lose reputation
        $blueteam = Team::find($attack->blueteam);
        $revLost = $blueteam->balance * .10 * -1;
        $blueteam->changeBalance($revLost);
        $blueteam->changeReputation($attack->reputation_loss);

        //Blueteam has revenue deduction bonus, starting at 20%
        $bonus->tags = ['RevenueDeduction'];
        $bonus->percentRevDeducted = 20;
        $bonus->save();
    }
}
