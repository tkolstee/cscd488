<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ScanAttack extends Attack {

    public $_name                   = "Scan";
    public $_class_name             = "Scan";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "SecInfo";
    public $_initial_difficulty     = 1;
    public $_initial_detection_risk = 2;
    public $_initial_analysis_risk  = 4;
    public $_initial_attribution_risk = 2;
    public $_initial_energy_cost    = 20;
    public $_initial_reputation_loss= -100;
    public $possible                = true;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
