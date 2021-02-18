<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class WirelessNetworkAttack extends Attack {

    public $_name                   = "Wireless Network";
    public $_class_name             = "WirelessNetwork";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "BasicAccess";
    public $_initial_success_chance = 0.6;
    public $_initial_detection_chance = 0.2;
    public $_initial_analysis_chance = 0.6;
    public $_initial_attribution_chance = 0.1;
    public $_initial_energy_cost    = 400;
    public $_help_text              = "Access the company's wireless network.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
