<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Team;

class ActionsOnObjective extends Payload 
{

    public $_name = "Actions on objective";
    public $_class_name = "ActionsOnObjective";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['RevenueSteal'];
        $bonus->percentRevStolen = 10;
        $bonus->save();
    }
}
