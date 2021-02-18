<?php

namespace App\Models\Assets;

use App\Models\Asset;

class RemoteWorkPolicyAsset extends Asset 
{

    public $_name    = "Remote Work Policy";
    public $_class_name = "RemoteWorkPolicy";
    public $_tags    = ['ExternalNetworkService'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 500;
    public $_ownership_cost = -200;
    public $_description = "Gives passive income each turn, and resist RequiresUserAction attacks. 10% weaker to physical attacks.";

    public function onPreAttack($attack)
    {
        if (in_array('RequiresUserAction', $attack->tags)){
            $attack->changeSuccessChance(-.1);
        }
        if (in_array('PhysicalAttack', $attack->tags)){
            $attack->changeSuccessChance(.1);
        }
    }
}
