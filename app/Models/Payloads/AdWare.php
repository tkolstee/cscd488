<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class AdWare extends Payload 
{

    public $_name = "Adware";
    public $_class_name = "AdWare";
    public $_tags = ['EndpointExecutable'];
    public $_percentIncreasedSuccess = 20;

    public function onAttackComplete($attack){
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['RevenueGeneration', 'UntilAnalyzed'];
        $bonus->revenueGenerated = 100;
        $bonus->save();
    }
}
