<?php

namespace App\Models\Assets;

use App\Models\Asset;

class ZeroDayAsset extends Asset 
{

    public $_name    = "0-day Exploit";
    public $_class_name = "ZeroDay";
    public $_tags    = ['Targeted'];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 2000;
    public $_ownership_cost = 0;
    public $_description = "Pay to discover a 0-day exploit to deploy against a target. Required for 0-day exploit attacks.";

    public function onPreAttack($attack)
    {
        
    }
}
