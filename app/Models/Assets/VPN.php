<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class VPN extends Asset 
{

    public $_name    = "Virtual Private Network";
    public $_class_name = "VPN";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 0;

    public function onPreAttack($attack)
    {
        if(!$attack->detected){
            $attack->difficulty -= 1;
        }
    }
}
