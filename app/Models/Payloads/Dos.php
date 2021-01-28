<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Bonus;

class Dos extends Payload 
{

    public $_name    = "Dos";
    public $_tags = [];

    public function onAttackComplete($attack){
        $bonus = parent::onAttackComplete($attack);
        $bonus->tags = ["OneTurnOnly", "RevenueDeduction", "DetectionDeduction"];
        $bonus->percentRevDeducted = 50;
        $bonus->percentDetDeducted = 20;
        $bonus->save();
    }
}
