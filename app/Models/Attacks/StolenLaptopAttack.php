<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class StolenLaptopAttack extends Attack {

    public $_name                   = "Stolen Laptop";
    public $_class_name             = "StolenLaptop";
    public $_tags                   = ['PhysicalAttack','AttackOnEndpoint'];
    public $_prereqs                = [];
    public $_payload_choice           = "StolenLaptop";
    public $_initial_success_chance = 0.25;
    public $_initial_detection_chance = 0.9;
    public $_initial_analysis_chance  = 0;
    public $_initial_attribution_chance = 0;
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
