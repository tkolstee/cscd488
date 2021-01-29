<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SupplyChainHwAttack extends Attack {

    public $_name                   = "Supply Chain - Hardware";
    public $_class_name             = "SupplyChainHw";
    public $_tags                   = [];
    public $_prereqs                = ['SecInfo'];
    public $_payload_tag           = 'ServerExecutable';
    public $_initial_difficulty     = 4.5;
    public $_initial_detection_risk = 1;
    public $_initial_analysis_risk  = 1;
    public $_initial_attribution_risk = 1;
    public $_initial_energy_cost    = 1000;
    public $_initial_reputation_loss= -100;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}