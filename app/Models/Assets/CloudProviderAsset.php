<?php

namespace App\Models\Assets;

use App\Models\Asset;

class CloudProviderAsset extends Asset 
{

    public $_name    = "Cloud Services Provider";
    public $_class_name = "CloudProvider";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 300;
    public $_ownership_cost = -200;

    public function onPreAttack($attack)
    {
        $attack->changeDetectionRisk(0.25);
        $attack->changeAnalysisRisk(0.25);
        $attack->changeAttributionRisk(0.25);
        if (in_array('PhysicalAttack', $attack->tags)){
            $attack->changeDifficulty(-.5);
        }
    }
}
