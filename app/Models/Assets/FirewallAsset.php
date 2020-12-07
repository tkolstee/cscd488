<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class FirewallAsset extends Asset {

    public $_name    = "Firewall";
    public $_class_name = "Firewall";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 0;

    function onPreAttack($attack) {
        if (in_array("ExternalNetworkProtocol", $attack->tags)) {
            $attack->difficulty += 2;
            Attack::updateAttack($attack);
        }
    }
}
