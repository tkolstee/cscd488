<?php

namespace App\Models\Payloads;

use App\Models\Team;
use App\Models\Inventory;
use App\Models\Payload;

class SecInfo extends Payload 
{

    public $_name    = "Security Info";
    public $_class_name = "SecInfo";
    public $_tags = [];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);
        //Detection and difficulty reduced by 20%, decreases 5% each turn
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['DetectionDeduction','DifficultyDeduction'];
        $bonus->percentDetDeducted = 20;
        $bonus->percentDiffDeducted = 20;
        $bonus->save();
        //Team gains Security Intelligence Asset for 5 turns
        Inventory::create([
            'team_id' => $attack->redteam,
            'info' => Team::find($attack->blueteam)->name,
            'asset_name' => 'SecurityIntelligence',
            'quantity' => 5
        ]);
    }
}
