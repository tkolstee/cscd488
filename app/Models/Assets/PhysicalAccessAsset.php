<?php

namespace App\Models\Assets;

use App\Models\Asset;

class PhysicalAccessAsset extends Asset {

    public $_name    = "Physical Access";
    public $_class_name = "PhysicalAccess";
    public $_tags    = ['Targeted'];
    public $_blue = 0;
    public $_buyable = 0;
    public $_purchase_cost = 0;
    public $_ownership_cost = 0;
    public $_description = "Gained from performing attacks.";

    function onPreAttack($attack) {
        parent::onPreAttack($attack);
    }
}
