<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Inventory;

class PrivAccess extends Payload 
{

    public $_name = "Privileged Access";
    public $_class_name = "PrivAccess";
    public $_tags = ['Executable', 'ServerHW'];

    public function onAttackComplete($attack){
        //Create privileged access token, belonging to redteam. Do not create bonus. 
        Inventory::create([
            'quantity' => 1,
            'team_id' => $attack->redteam,
            'asset_name' => 'AccessToken',
            'level' => 2,
        ]);
    }
}
