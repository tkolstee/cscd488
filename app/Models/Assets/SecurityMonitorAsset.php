<?php

namespace App\Models\Assets;

use App\Models\Asset;

class SecurityMonitorAsset extends Asset 
{

    public $_name    = "Security Monitor";
    public $_class_name = "SecurityMonitor";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 50;
    public $_ownership_cost = 10;
    public $_description = "Guarantee an attack will be detected if detection chance was at least 50%.";

    public function onPreAttack($attack)
    {
        if($attack->calculated_detection_risk > 0.5){
            $attack->changeDetectionChance(1);
        }
    }
}
