<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ScanAttack extends Attack {

    public $_name                   = "Scan";
    public $_class_name             = "Scan";
    public $_tags                   = ['FirewallDefends'];
    public $_prereqs                = [];
    public $_payload_choice           = "SecInfo";
    public $_initial_success_chance = 1;
    public $_initial_detection_chance = 0.5;
    public $_initial_analysis_chance  = 0.9;
    public $_initial_attribution_chance = 0.1;
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
