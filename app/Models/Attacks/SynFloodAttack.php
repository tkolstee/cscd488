<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SynFloodAttack extends Attack {

    public $_name                   = "Syn Flood";
    public $_class_name             = "SynFlood";
    public $_tags                   = ['ExternalNetworkProtocol', 'DenialOfService'];
    public $_prereqs                = [];
    public $_initial_success_chance = 0.6;
    public $_initial_detection_chance = 1;
    public $_initial_energy_cost    = 100;

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
