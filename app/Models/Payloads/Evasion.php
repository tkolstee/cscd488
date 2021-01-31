<?php

namespace App\Models\Payloads;

use App\Models\Team;
use App\Models\Payload;

class Evasion extends Payload 
{

    public $_name = "Detection Evasion";
    public $_class_name = "Evasion";
    public $_tags = ['Executable','ServerHW'];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['DetectionDeduction'];
        $bonus->percentDetDeducted = 30;
        $bonus->save();
    }
}
