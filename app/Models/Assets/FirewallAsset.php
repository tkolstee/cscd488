<?php

use App\Models\Asset;

class AssetFirewall extends Asset {

    function onPreAttack($attack) {
        if (in_array("ExternalNetworkProtocol", $attack->tags)) {
            $attack->difficulty += 2;
        }
    }

}
