<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Bonus;

class MaliciousInsider extends Payload 
{

    public $_name    = "MaliciousInsider";
    public $_tags = [];

    public function onAttackComplete($attack){
        $bouns = parent::onAttackComplete($attack);
        
    }
}
