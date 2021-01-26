<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class Ransomware extends Payload 
{

    public $_name    = "Ransomware";
    public $_tags = ['EndpointExecutable'];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
    }
}
