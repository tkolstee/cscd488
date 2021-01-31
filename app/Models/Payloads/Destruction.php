<?php

namespace App\Models\Payloads;

use App\Models\Team;
use App\Models\Payload;

class Destruction extends Payload 
{

    public $_name = "Resource Destruction";
    public $_class_name = "Destruction";
    public $_tags = ['DBAttack','EndpointExecutable'];
    public $_percentRevLost = 10;

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);

        //Blueteam has revenue deduction bonus, starting at 20%
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['RevenueDeduction'];
        $bonus->percentRevDeducted = 20;
        $bonus->save();
    }
}
