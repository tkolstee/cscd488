<?php

namespace App\Models\Assets;

use App\Models\Asset;

class OnPremDataCenterAsset extends Asset 
{

    public $_name    = "On-Premises Data Center";
    public $_class_name = "OnPremDataCenter";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 10000;
    public $_ownership_cost = -1000;
    public $_description = "Helps detect, analyze, and attribute attacks, but makes you slightly weaker to physical attacks.";

    public function onPreAttack($attack)
    {
        $attack->changeDetectionChance(0.2);
        $attack->changeAnalysisChance(0.2);
        $attack->changeAttributionChance(0.2);
        if (in_array('PhysicalAttack', $attack->tags)){
            $attack->changeSuccessChance(.1);
        }
    }
}
