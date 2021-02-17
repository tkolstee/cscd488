<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class MaliciousInsiderAttack extends Attack {

    public $_name                   = "Malicious Insider";
    public $_class_name             = "MaliciousInsider";
    public $_tags                   = ['PhysicalAttack'];
    public $_prereqs                = [];
    public $_payload_choice         = 'MaliciousInsider';
    public $_initial_success_chance = 2;
    public $_initial_detection_chance = 2.5;
    public $_initial_analysis_chance  = 5;
    public $_initial_attribution_chance = 4;
    public $_initial_energy_cost    = 1000;
    public $_help_text              = "Get hired as an employee.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
