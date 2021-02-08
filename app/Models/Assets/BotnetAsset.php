<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class BotnetAsset extends Asset {

    public $_name    = "Botnet";
    public $_class_name = "Botnet";
    public $_tags    = [];
    public $_blue = 0;
    public $_buyable = 1;
    public $_purchase_cost = 200;
    public $_ownership_cost = 0;
    public $_description = "A network of lots of computers infected by malware so you can command them to execute attacks.
        Enables DDoS attack.";

    function onPreAttack($attack) {
        parent::onPreAttack();
    }
}
