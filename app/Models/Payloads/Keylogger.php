<?php

namespace App\Models\Payloads;

use App\Models\Payload;

class Keylogger extends Payload 
{

    public $_name = "Keylogger";
    public $_class_name = "Keylogger";
    public $_tags = ['EndpointExecutable','OfficeHW'];

    public function onAttackComplete($attack){
        $bonus = parent::onAttackComplete($attack);
        $bonus->tags = ['UntilAnalyzed', 'AddTokens'];
        $bonus->save();
    }
}
