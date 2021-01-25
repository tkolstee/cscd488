<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class PRDeptAsset extends Asset 
{

    public $_name    = "Personal Relations Dept.";
    public $_class_name = "PRDept";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 200;
    public $_ownership_cost = 20;

    public function onPreAttack($attack)
    {
        if($attack->calculated_detection_risk > 3){
            $attack->changeDifficulty(.1);
        }
    }
}
