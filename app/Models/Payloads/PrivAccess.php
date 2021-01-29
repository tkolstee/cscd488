<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Team;

class PrivAccess extends Payload 
{

    public $_name = "Privileged Access";
    public $_class_name = "PrivAccess";
    public $_tags = ['Executable', 'ServerHW'];

    public function onAttackComplete($attack){
        //Create privileged access token, belonging to redteam. Do not create bonus. 
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $redteam->addToken($blueteam->name, 2);
    }
}
