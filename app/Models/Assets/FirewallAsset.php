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
    public $_purchase_cost = 200;
    public $_ownership_cost = 100;

    function onPreAttack($attack) {
        if (in_array("FirewallDefends", $attack->tags)) {
            $attack->changeDifficulty(.2);
        }
        if (in_array('Internal', $attack->tags) || in_array('PrivilegedAccess', $attack->tags) || in_array('PwnedAccess', $attack->tags)){
            $attack->changeDetectionRisk(.15);
            $attack->changeAnalysisRisk(.15);
        }
    }
}
