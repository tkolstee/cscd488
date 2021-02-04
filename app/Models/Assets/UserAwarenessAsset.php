<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class UserAwarenessAsset extends Asset {

    public $_name    = "User Awareness Training";
    public $_class_name = "UserAwareness";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 100;

    function onPreAttack($attack) {
        if (in_array("TargetsEndpoints", $attack->tags)) {
            $attack->changeDifficulty(.2);
        }
        if (in_array('SocialEngineering', $attack->tags)){
            $attack->changeDifficulty(.3);
            $attack-> changeDetectionRisk(.1);
            $attack-> changeAnalysisRisk(.1);
        }
        if( in_array('PhysicalAttack', $attack->tags) || $attack->class_name == "MaliciousInsider"){
            $attack->changeDifficulty(.1);
        }
    }
}
