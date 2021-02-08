<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class EndpointProtectionAsset extends Asset {

    public $_name    = "Endpoint Protection";
    public $_class_name = "Endpoint Protection";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 200;
    public $_description = "Protects and helps detect attacks with AttackOnEndpoint tag by 100%.";

    function onPreAttack($attack) {
        if (in_array("AttackOnEndpoint", $attack->tags)) {
            $attack->changeDifficulty(1);
            $attack->changeDetectionRisk(1);
            $attack->changeAnalysisRisk(1);
        }
    }
}
