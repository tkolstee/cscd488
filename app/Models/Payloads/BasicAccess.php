<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Inventory;

class BasicAccess extends Payload 
{

    public $_name    = "BasicAccess";
    public $_tags = ['Executable', 'ServerHW', 'OfficeHW'];

    public function onAttackComplete($attack){
        //Create basic access token, belonging to redteam. Do not create bonus. 
        Inventory::create([
            'quantity' => 1,
            'team_id' => $attack->redteam,
            'asset_name' => 'AccessToken',
        ]);
    }
}
