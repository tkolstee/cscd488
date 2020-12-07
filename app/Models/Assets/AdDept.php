<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class AdDept extends Asset 
{

    public $_name    = "Advertising Dept.";
    public $_class_name = "AdDept";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 300;
    public $_ownership_cost = -50;

    public function onPreAttack($attack)
    {
        if($attack->detected){
            $attack->difficulty += 1;
        }
    }
}
