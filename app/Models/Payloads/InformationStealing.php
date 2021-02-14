<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class InformationStealing extends Payload 
{

    public $_name = "Information Stealing";
    public $_class_name = "InformationStealing";
    public $_tags = ['DBAttack'];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['DetectionDeduction', 'DifficultyDeduction'];
        $bonus->percentDetDeducted = 20;
        $bonus->percentDiffDeducted = 20;
        $bonus->save();
    }
}
