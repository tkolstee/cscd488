<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class TestAttackAsset extends Asset 
{

    public $_name    = "Test Attack";
    public $_class_name = "TestAttack";
    public $_tags    = ["offensive tag 1", "offensive tag 2"];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 0;

    public function onPreAttack($attack)
    {
        
    }
}
