<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SupplyChainSwAttack extends Attack {

    public $_name                   = "Supply Chain - Software";
    public $_class_name             = "SupplyChainSw";
    public $_tags                   = [];
    public $_prereqs                = ['SecurityIntelligence'];
    public $_payload_tag           = 'ServerExecutable';
    public $_initial_success_chance = 0.1;
    public $_initial_detection_chance = 0.3;
    public $_initial_analysis_chance  = 0.6;
    public $_initial_attribution_chance = 0.5;
    public $_initial_energy_cost    = 750;
    public $_help_text              = "Compromise off the shelf software used by a company.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
