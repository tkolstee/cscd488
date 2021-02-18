<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class EavesdroppingAttack extends Attack {

    public $_name                   = "Eavesdropping";
    public $_class_name             = "Eavesdropping";
    public $_tags                   = [];
    public $_prereqs                = ['Internal', 'PrivilegedAccess'];
    public $_payload_choice         = 'Eavesdropping';
    public $_initial_success_chance = 0.7;
    public $_initial_detection_chance = 0.2;
    public $_initial_analysis_chance  = 0.7;
    public $_initial_attribution_chance = 0.2;
    public $_initial_energy_cost    = 400;
    public $_help_text              = "Spy on network traffic for credentials or info.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
