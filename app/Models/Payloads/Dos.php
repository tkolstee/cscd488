<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Bonus;

class Dos extends Payload 
{

    public $_name    = "Dos";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        $bonus = new Bonus();
        $bonus->payload_name = "Dos";
        $bonus->team_id = $attack->redteam;
        $bonus->target_id  = $attack->blueteam;
        $bonus->tags = ["OneTurnOnly", "RevenueDeduction", "DetectionDeduction"];
        $bonus->percentRevDeducted = .5;
        $bonus->percentDetDeducted = .2;
        $bonus->save();
    }
}
