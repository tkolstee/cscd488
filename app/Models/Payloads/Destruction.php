<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class Destruction extends Payload 
{

    public $_name    = "Destruction";
    public $_tags = ['DBAttack','EndpointExecutable'];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
    }
}
