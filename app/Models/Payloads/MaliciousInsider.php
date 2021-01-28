<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class MaliciousInsider extends Payload 
{
    
    public $_name = "Malicious Insider";
    public $_class_name = "MaliciousInsider";
    public $_tags = [];

    public function onAttackComplete($attack){
        $bouns = parent::onAttackComplete($attack);
        
    }
}
