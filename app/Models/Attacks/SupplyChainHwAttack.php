<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SupplyChainHwAttack extends Attack {

    public $_name                   = "Supply Chain - Hardware";
    public $_class_name             = "SupplyChainHw";
    public $_tags                   = [];
    public $_prereqs                = ['SecInfo'];
    public $_payload_tag           = 'ServerExecutable';
    public $_initial_success_chance = 4.5;
    public $_initial_detection_chance = 1;
    public $_initial_analysis_chance  = 1;
    public $_initial_attribution_chance = 1;
    public $_initial_energy_cost    = 1000;
    public $_help_text              = "Compromise the hardware a company buys at the factory or during shipping.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
