<?php

namespace App\Models\Assets;

use App\Models\Asset;

class SecurityIntelligenceAsset extends Asset 
{
    public $_name    = "Security Intelligence";
    public $_class_name = "SecurityIntelligence";
    public $_tags    = ['Targeted','TurnConsumable'];
    public $_blue = 0;
    public $_buyable = 0;
    public $_purchase_cost = 0;
    public $_ownership_cost = 0;
    public $_description = "Represents security intelligence gained from probing a blue team. Not purchasable.";

    public function onPreAttack($attack)
    {
        
    }
}
