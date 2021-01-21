<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class FootholdAsset extends Asset 
{

    public $_name    = "Foothold";
    public $_class_name = "Foothold";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 0;
    public $_purchase_cost = 0;
    public $_ownership_cost = 0;

    public function onPreAttack($attack)
    {
        
    }
}
