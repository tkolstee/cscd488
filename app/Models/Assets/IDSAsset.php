<?php

namespace App\Models\Assets;

use App\Models\Asset;

class IDSAsset extends Asset {

    public $_name    = "Intrusion Detection System";
    public $_class_name = "IDS";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 200;
    public $_ownership_cost = -200;

    function onPreAttack($attack) {
        
    }
}
