<?php

namespace App\Models\Assets;

use App\Models\Asset;

class PhysicalAccessPolicyAsset extends Asset {

    public $_name    = "Physical Access Policy";
    public $_class_name = "PhysicalAccessPolicy";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 100;
    public $_description = "Protects against attacks with PhysicalAttack tag by 20%.";

    function onPreAttack($attack) {
        if (in_array("PhysicalAttack", $attack->tags)) {
            $attack->changeSuccessChance(-.2);
        }
    }
}
