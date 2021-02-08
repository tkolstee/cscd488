<?php

namespace App\Models\Assets;

use App\Models\Asset;

class SecurityAnalystAsset extends Asset 
{
    public $_name    = "Security Analyst";
    public $_class_name = "SecurityAnalyst";
    public $_tags    = ['Analysis'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 300;
    public $_ownership_cost = 400;
    public $_description = "Protects against all attacks by 10% and increases chance of detection and analysis by 30%.";

    public function onPreAttack($attack)
    {
        parent::onPreAttack($attack);
        $attack->changeDifficulty(.1);
        $attack->changeDetectionRisk(.3);
        $attack->changeAnalysisRisk(.3);
    }
}
