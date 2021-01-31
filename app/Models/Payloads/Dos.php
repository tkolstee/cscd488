<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class Dos extends Payload 
{

    public $_name = "Denial of Service";
    public $_class_name = "Dos";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        $bonus = parent::createBonus($attack);
        $bonus->tags = ["OneTurnOnly", "RevenueDeduction", "DetectionDeduction"];
        $bonus->percentRevDeducted = 50;
        $bonus->percentDetDeducted = 20;
        $bonus->save();
    }
}
