<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class OsintAttack extends Attack {

    public $_name                   = "OSINT";
    public $_class_name             = "Osint";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "SecInfo";
    public $_initial_difficulty     = 4;
    public $_initial_detection_risk = 0;
    public $_initial_analysis_risk  = 0;
    public $_initial_attribution_risk = 0;
    public $_initial_energy_cost    = 30;

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
