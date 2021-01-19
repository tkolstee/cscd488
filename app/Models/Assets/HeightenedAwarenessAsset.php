<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class HeightenedAwarenessAsset extends Asset 
{

    public $_name    = "Heightened Awareness";
    public $_class_name = "HeightenedAwareness";
    public $_tags    = ['TurnConsumable','Targeted'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 150;
    public $_ownership_cost = 0;

    public function onPreAttack($attack)
    {
        
    }
}
