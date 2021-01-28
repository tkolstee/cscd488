<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Bonus;

class Xss extends Payload 
{

    public $_name    = "Xss";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        $bonus = new Bonus();
        $bonus->attack_id = $attack->id;
        $bonus->payload_name = "Xss";
        $bonus->team_id = $attack->redteam;
        $bonus->target_id  = $attack->blueteam;
        $bonus->tags = ["UntilAnalyzed", "RevenueSteal"];
        $bonus->save();
    }
}
