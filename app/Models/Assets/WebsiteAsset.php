<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class WebsiteAsset extends Asset 
{

    public $_name    = "Website";
    public $_class_name = "Website";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 200;
    public $_ownership_cost = -500;
    public $_description = "A website for your company that generates revenue.";

    public function onPreAttack($attack)
    {
        
    }
}
