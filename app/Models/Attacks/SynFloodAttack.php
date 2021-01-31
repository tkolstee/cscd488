<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SynFloodAttack extends Attack {

    public $_name                   = "Syn Flood";
    public $_class_name             = "SynFlood";
    public $_tags                   = ['ExternalNetworkProtocol', 'DenialOfService'];
    public $_prereqs                = [];
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 5;
    public $_initial_energy_cost    = 100;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
