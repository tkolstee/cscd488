<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class DataCenterIntrusionAttack extends Attack {

    public $_name                   = "Datacenter Intrusion";
    public $_class_name             = "DataCenterIntrusion";
    public $_tags                   = ['PhysicalAttack'];
    public $_prereqs                = ['PhysicalAccess'];
    public $_initial_success_chance = 0.35;
    public $_initial_detection_chance = 0.3;
    public $_initial_analysis_chance  = 0.70;
    public $_initial_attribution_chance = 0.25;
    public $_initial_energy_cost    = 200;
    public $_help_text              = "Perform a physical attack on the target's data center. Requires physical access.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
