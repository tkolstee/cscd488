<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class StolenLaptopAttack extends Attack {

    public $_name                   = "Stolen Laptop";
    public $_class_name             = "StolenLaptop";
    public $_tags                   = ['PhysicalAttack','AttackOnEndpoint'];
    public $_prereqs                = [];
    public $_payload_choice           = "StolenLaptop";
    public $_initial_difficulty     = 0;
    public $_initial_detection_risk = 4.5;
    public $_initial_analysis_risk  = 0;
    public $_initial_attribution_risk = 0;
    public $_initial_energy_cost    = 500;
    public $_help_text              = "Steal an employees laptop and harvest it for credentials/access.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
