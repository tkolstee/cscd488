<?php

namespace App\Models\Assets;

use App\Models\Asset;

class AdDeptAsset extends Asset 
{

    public $_name    = "Advertising Dept.";
    public $_class_name = "AdDept";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 300;
    public $_ownership_cost = -50;
    public $_description = "Advertising Dept. gives you passive income each turn.";

    public function onPreAttack($attack)
    {
        
    }
}
