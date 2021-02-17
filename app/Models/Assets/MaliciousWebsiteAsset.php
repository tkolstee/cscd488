<?php

namespace App\Models\Assets;

use App\Models\Asset;

class MaliciousWebsiteAsset extends Asset 
{

    public $_name    = "Malicious Website";
    public $_class_name = "MaliciousWebsite";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 500;
    public $_ownership_cost = 0;
    public $_description = "Create a malicious website to redirect victims to.";

    public function onPreAttack($attack)
    {
        
    }
}
