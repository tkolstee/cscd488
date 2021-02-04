<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ScanAttack extends Attack {

    public $_name                   = "Scan";
    public $_class_name             = "Scan";
    public $_tags                   = ['FirewallDefends'];
    public $_prereqs                = [];
    public $_payload_choice           = "SecInfo";
    public $_initial_difficulty     = 0;
    public $_initial_detection_risk = 2.5;
    public $_initial_analysis_risk  = 4.5;
    public $_initial_attribution_risk = 0.5;
    public $_initial_energy_cost    = 20;
    public $_help_text              = "Find information on services and hosts reachable from outside.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
