<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;
use App\Models\Team;

class HeightenedAwarenessAsset extends Asset 
{

    public $_name    = "Heightened Awareness";
    public $_class_name = "HeightenedAwareness";
    public $_tags    = ['TurnConsumable','Targeted'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 150;
    public $_ownership_cost = 0;
    public $_description = "Protects by 10% and increases Detection Chance by 20% against selected target. One consumed per turn.";

    public function onPreAttack($attack)
    {
        $redteam = Team::find($attack->redteam);
        if($redteam->name == $attack->info){
            $attack->changeDifficulty(.1);
            $attack->changeDetectionRisk(.2);
        }
    }
}
