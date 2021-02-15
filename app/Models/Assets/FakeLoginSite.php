<?php

namespace App\Models\Assets;

use App\Models\Asset;

class FakeLoginSite extends Asset 
{

    public $_name    = "Fake Login Website";
    public $_class_name = "FakeLoginSite";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 200;
    public $_ownership_cost = 0;

    public function onPreAttack($attack)
    {
        
    }
}
