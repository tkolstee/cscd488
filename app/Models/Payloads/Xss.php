<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Bonus;

class Xss extends Payload 
{

    public $_name    = "Xss";
    public $_tags = [];

    public function onAttackComplete($attack){
        $bonus = parent::onAttackComplete($attack);
        $bonus->tags = ["UntilAnalyzed", "RevenueSteal"];
        $bonus->save();
    }
}
