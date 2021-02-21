<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class InsiderAsset extends Asset 
{

    public $_name    = "Insider";
    public $_class_name = "Insider";
    public $_tags    = ['AccessToken','Targeted'];
    public $_blue = 0;
    public $_buyable = 0;
    public $_description = "Man on the inside who is working for a red team.";

    public function onPreAttack($attack)
    {
        
    }
}
