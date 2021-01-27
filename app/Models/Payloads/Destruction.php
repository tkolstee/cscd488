<?php

namespace App\Models\Payloads;

use App\Models\Bonus;
use App\Models\Team;
use App\Models\Payload;

class Destruction extends Payload 
{

    public $_name    = "Destruction";
    public $_tags = ['DBAttack','EndpointExecutable'];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        //Blueteam loses 10% of revenue immediately and lose reputation
        $blueteam = Team::find($attack->blueteam);
        $revLost = $blueteam->balance * .10 * -1;
        $blueteam->changeBalance($revLost);
        $blueteam->changeReputation($attack->reputation_loss);

        //Blueteam has revenue deduction bonus, starting at 20%
        $bonus = new Bonus;
        $bonus->payload_name = $this->_name;
        $bonus->team_id = $attack->redteam;
        $bonus->target_id  = $attack->blueteam;
        $bonus->tags = ['RevenueDeduction'];
        $bonus->percentRevDeducted = 20;
        $bonus->save();
    }
}
