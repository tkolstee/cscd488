<?php

namespace App\Models\Assets;

use App\Models\Asset;

class SecurityAnalystAsset extends Asset 
{
    public $_name    = "Security Analyst";
    public $_class_name = "SecurityAnalyst";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;

    public function onPreAttack($attack)
    {
        parent::onPreAttack($attack);
    }
}
