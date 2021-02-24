<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class DosAttack extends Attack {

    public $_name                   = "DoS";
    public $_class_name             = "Dos";
    public $_tags                   = ['FirewallDefends'];
    public $_prereqs                = [];
    public $_payload_choice           = "Dos";
    public $_initial_success_chance = 0.75;
    public $_initial_detection_chance = 1;
    public $_initial_analysis_chance = 0.9;
    public $_initial_attribution_chance = 0.2;
    public $_initial_energy_cost    = 200;
    public $_help_text              = "Take the company offline by passing expensive or excessive traffic.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
