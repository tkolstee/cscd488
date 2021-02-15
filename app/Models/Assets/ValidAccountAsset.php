<?php

namespace App\Models\Assets;

use App\Models\Asset;

class ValidAccountAsset extends Asset {

    public $_name    = "Valid Account";
    public $_class_name = "ValidAccount";
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
