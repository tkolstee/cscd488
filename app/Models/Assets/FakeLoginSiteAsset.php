<?php

namespace App\Models\Assets;

use App\Models\Asset;

class FakeLoginSiteAsset extends Asset 
{

    public $_name    = "Fake Login Website";
    public $_class_name = "FakeLoginSite";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 200;
    public $_ownership_cost = 0;
    public $_description = "Create a fake login site to redirect victims to. Often used for phishing attacks.";

    public function onPreAttack($attack)
    {
        
    }
}
