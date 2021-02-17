<?php

namespace App\Models\Payloads;

use App\Models\Payload;
use App\Models\Inventory;
use App\Models\Team;

class MaliciousInsider extends Payload 
{
    
    public $_name = "Malicious Insider";
    public $_class_name = "MaliciousInsider";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        $blueteam = Team::find($attack->blueteam);
        Inventory::create([
            'quantity' => 1,
            'team_id' => $attack->redteam,
            'asset_name' => 'PhysicalAccess',
            'level' => 1,
            'info' => $blueteam->name
        ]);
        Inventory::create([
            'quantity' => 1,
            'team_id' => $attack->redteam,
            'asset_name' => 'ValidAccount',
            'level' => 1,
            'info' => $blueteam->name
        ]);
    }
}
