<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class FuzzingAttack extends Attack {

    public $_name                   = "Fuzzing";
    public $_class_name             = "Fuzzing";
    public $_tags                   = [];
    public $_prereqs                = ['ExternalNetworkService'];
    public $_payload_choice         = 'Fuzzing';
    public $_initial_success_chance = 0.3;
    public $_initial_detection_chance = 0.6;
    public $_initial_analysis_chance = 0.2;
    public $_initial_attribution_chance = 0.1;
    public $_initial_energy_cost    = 200;
    public $_help_text              = "Exploit a service using randomized data.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
