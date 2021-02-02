<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SupplyChainSwAttack extends Attack {

    public $_name                   = "Supply Chain - Software";
    public $_class_name             = "SupplyChainSw";
    public $_tags                   = [];
    public $_prereqs                = ['SecInfo'];
    public $_payload_tag           = 'ServerExecutable';
    public $_initial_difficulty     = 4.5;
    public $_initial_detection_risk = 1.5;
    public $_initial_analysis_risk  = 3;
    public $_initial_attribution_risk = 2.5;
    public $_initial_energy_cost    = 750;

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
