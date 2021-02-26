<?php

namespace App\Models\Assets;

use App\Models\Asset;

class FirewallAsset extends Asset {

    public $_name    = "Firewall";
    public $_class_name = "Firewall";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 200;
    public $_ownership_cost = 100;
    public $_description = "Defends against attacks with tag FirewallDefends by 20%, and 15% greater chance attacks using
        Access Tokens will be detected and analyzed.";

    function onPreAttack($attack) {
        if ($attack->hasTag("FirewallDefends")) {
            $attack->changeSuccessChance(-.2);
        }
        if ($attack->hasTag("Internal")){
            $attack->changeDetectionChance(.15);
            $attack->changeAnalysisChance(.15);
        }
    }
}
