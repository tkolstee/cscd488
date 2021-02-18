<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class VPNAsset extends Asset 
{

    public $_name    = "Virtual Private Network";
    public $_class_name = "VPN";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 0;
    public $_description = "Allows users to send and receive data across shared networks as if 
        they were directly connected to the private network.";

    public function onPreAttack($attack)
    {
        if(in_array('SQLInjection', $attack->tags)) $attack->changeDetectionChance(-.2);
        if(in_array('ExternalNetworkProtocol', $attack->tags)) $attack->changeDetectionChance(-.2);
    }
}
