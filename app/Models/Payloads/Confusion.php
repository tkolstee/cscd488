<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class Confusion extends Payload 
{

    public $_name = "Confusion";
    public $_class_name = "Confusion";
    public $_tags = ['Executable','ServerHW'];

    public function onAttackComplete($attack){
        $bonus = parent::onAttackComplete($attack);
        $bonus->tags = ['DetectionDeduction', 'AnalysisDeduction'];
        $bonus->percentDetDeducted = 20;
        $bonus->percentAnalDeducted = 20;
        $bonus->save();
    }
}
