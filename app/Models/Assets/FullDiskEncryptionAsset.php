<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class FullDiskEncryptionAsset extends Asset {

    public $_name    = "Full Disk Encryption";
    public $_class_name = "FullDiskEncryption";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 0;
    public $_description = "Stolen Laptop attack chance reduces to 20%, or 3% with Strong Password Policy.";

    function onPreAttack($attack) {
        if ($attack->class_name == "StolenLaptop") {
            $attack->calculated_difficulty = 4;
            $blueteam = Team::find($attack->blueteam);
            $invs = $blueteam->inventories();
            foreach($invs as $inv){
                if($inv->asset_name == "StrongPassword"){
                    $attack->calculated_difficulty = 4.85;
                }
            }
            Attack::updateAttack($attack);
        }
    }
}
