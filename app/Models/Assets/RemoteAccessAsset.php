<?php

namespace App\Models\Assets;

use App\Models\Asset;

class RemoteAccessAsset extends Asset {

    public $_name    = "Remote Access";
    public $_class_name = "RemoteAccess";
    public $_tags    = ['ExternalNetworkService'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = -200;
    public $_percent_revenue_bonus = 10;

    function onPreAttack($attack) {
        $attack->changeSuccessChance(.1);
    }
}
