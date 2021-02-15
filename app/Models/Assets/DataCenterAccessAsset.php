<?php

namespace App\Models\Assets;

use App\Models\Asset;

class DataCenterAccessAsset extends Asset {

    public $_name    = "Datacenter Access";
    public $_class_name = "DataCenterAccess";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 0;
    public $_purchase_cost = 0;
    public $_ownership_cost = 0;
    public $_description = "Gained from performing attacks.";

    function onPreAttack($attack) {
        parent::onPreAttack($attack);
    }
}
