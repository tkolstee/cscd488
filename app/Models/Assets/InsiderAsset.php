<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class InsiderAsset extends Asset 
{

    public $_name    = "Insider";
    public $_class_name = "Insider";
    public $_tags    = ['AccessToken'];
    public $_blue = 0;
    public $_buyable = 0;

    public function onPreAttack($attack)
    {
        
    }
}
