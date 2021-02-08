<?php

namespace App\Models\Assets;

use App\Models\Asset;

class RemoteWorkPolicyAsset extends Asset 
{

    public $_name    = "Remote Work Policy";
    public $_class_name = "RemoteWorkPolicy";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 500;
    public $_ownership_cost = -200;

    public function onPreAttack($attack)
    {
        if (in_array('RequiresUserAction', $attack->tags)){
            $attack->changeDifficulty(.1);
        }
        if (in_array('PhysicalAttack', $attack->tags)){
            $attack->changeDifficulty(-.1);
        }
    }
}
