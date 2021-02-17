<?php

namespace App\Models\Assets;

use App\Models\Asset;

class AccessTokenAsset extends Asset 
{
    /**
     * Access Token type is denoted by level. 
     * 1 = Basic
     * 2 = Privileged
     * 3 = Pwnd
     */
    public $_name    = "Access Token";
    public $_class_name = "AccessToken";
    public $_tags    = ['Targeted'];
    public $_blue = 0;
    public $_buyable = 0;
    public $_purchase_cost = 0;
    public $_ownership_cost = 0;

    public function onPreAttack($attack)
    {
        
    }
}
