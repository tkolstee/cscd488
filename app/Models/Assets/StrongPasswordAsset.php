<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class StrongPasswordAsset extends Asset {

    public $_name    = "Strong Password Policy";
    public $_class_name = "StrongPassword";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 50;

    function onPreAttack($attack) {
        if ($attack->class_name == "PasswordGuessing") {
            $attack->changeDifficulty(.5);
        }
    }
}