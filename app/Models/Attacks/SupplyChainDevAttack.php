<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SupplyChainDevAttack extends Attack {

    public $_name                   = "Supply Chain - Dev Tools";
    public $_class_name             = "SupplyChainDev";
    public $_tags                   = [];
    public $_prereqs                = ['SecurityIntelligence'];
    public $_payload_tag           = 'ServerExecutable';
    public $_initial_success_chance = 0.1;
    public $_initial_detection_chance = 0.5;
    public $_initial_analysis_chance  = 0.75;
    public $_initial_attribution_chance = 0.6;
    public $_initial_energy_cost    = 500;
    public $_help_text              = "Compromise a tool or dependency used by the company's software developement team.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
