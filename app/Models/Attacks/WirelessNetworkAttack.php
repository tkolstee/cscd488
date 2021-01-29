<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class WirelessNetworkAttack extends Attack {

    public $_name                   = "Wireless Network";
    public $_class_name             = "WirelessNetwork";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "BasicAccess";
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 1;
    public $_initial_analysis_risk  = 3;
    public $_initial_attribution_risk = 1;
    public $_initial_energy_cost    = 400;
    public $_initial_reputation_loss= -100;
    public $possible                = true;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
