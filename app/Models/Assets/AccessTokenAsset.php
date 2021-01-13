<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class AccessTokenAsset extends Asset 
{

    public $_name    = "Access Token";
    public $_class_name = "AccessToken";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 0;

    public function onPreAttack($attack)
    {
        
    }
}
