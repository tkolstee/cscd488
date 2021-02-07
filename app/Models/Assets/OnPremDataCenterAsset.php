<?php

namespace App\Models\Assets;

use App\Models\Asset;

class OnPremDataCenterAsset extends Asset 
{

    public $_name    = "On-Premises Data Center";
    public $_class_name = "OnPremDataCenter";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 10000;
    public $_ownership_cost = -1000;

    public function onPreAttack($attack)
    {
        $attack->changeDifficulty(0.2);
    }
}
