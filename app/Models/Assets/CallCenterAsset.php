<?php

namespace App\Models\Assets;

use App\Models\Asset;

class CallCenterAsset extends Asset 
{

    public $_name    = "Call Center";
    public $_class_name = "CallCenter";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 2000;
    public $_ownership_cost = -1500;

    public function onPreAttack($attack)
    {
        if (in_array('RequiresUserAction', $attack->tags)){
            $attack->changeSuccessChance(-.2);
        }
        if (in_array('PhysicalAttack', $attack->tags)){
            $attack->changeSuccessChance(-.1);
        }
    }
}
