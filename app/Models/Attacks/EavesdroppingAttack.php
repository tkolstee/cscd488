<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class EavesdroppingAttack extends Attack {

    public $_name                   = "Eavesdropping";
    public $_class_name             = "Eavesdropping";
    public $_tags                   = [];
    public $_prereqs                = ['Internal', 'PrivilegedAccess'];
    public $_payload_choice         = 'Eavesdropping';
    public $_initial_difficulty     = 1.5;
    public $_initial_detection_risk = 1;
    public $_initial_analysis_risk  = 3.5;
    public $_initial_attribution_risk = 1;
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
