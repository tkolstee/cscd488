<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class DataCenterIntrusionAttack extends Attack {

    public $_name                   = "Datacenter Intrusion";
    public $_class_name             = "DataCenterIntrusion";
    public $_tags                   = ['PhysicalAttack'];
    public $_prereqs                = ['PhysicalAccess'];
    public $_payload_choice           = "Dos";
    public $_initial_difficulty     = 3.25; //35% chance of success
    public $_initial_detection_risk = 3.5; //30%
    public $_initial_analysis_risk  = 1.5; //70%
    public $_initial_attribution_risk = 3.75; //25%
    public $_initial_energy_cost    = 200;
    public $_help_text              = "Perform a physical attack on the target's data center. Requires physical access.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
