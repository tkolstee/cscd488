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
    public $_description = "Protects against attacks containing tag TargetsEndpoints by 20%, PhysicalAttack by 10%,
        and SocialEngineering by 30%. Also increases chance of detection and analysis for SocialEngineering by 10%.";

    function onPreAttack($attack) {
        if (in_array("TargetsEndpoints", $attack->tags)) {
            $attack->changeSuccessChance(-.2);
        }
        if (in_array('SocialEngineering', $attack->tags)){
            $attack->changeSuccessChance(-.3);
            $attack-> changeDetectionChance(.1);
            $attack-> changeAnalysisChance(.1);
        }
        if( in_array('PhysicalAttack', $attack->tags) || $attack->class_name == "MaliciousInsider"){
            $attack->changeSuccessChance(-.1);
        }
    }
}
