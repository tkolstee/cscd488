<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Team;

class BasicAccess extends Payload 
{

    public $_name = "Bassic Access";
    public $_class_name = "BasicAccess";
    public $_tags = ['Executable', 'ServerHW', 'OfficeHW'];

    public function onAttackComplete($attack){
        //Create basic access token, belonging to redteam. Do not create bonus. 
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $redteam->addToken($blueteam->name, 1);
    }
}
