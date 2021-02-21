<?php

namespace App\Models\Assets;

use App\Models\Asset;

class TestDefenseAsset extends Asset 
{

    public $_name    = "Test Defense";
    public $_class_name = "TestDefense";
    public $_tags    = ["defensive tag 1"];
    public $_blue = 1;
    public $_buyable = 0;
    public $_purchase_cost = 100;
    public $_ownership_cost = 0;

    public function onPreAttack($attack)
    {
        
    }
}