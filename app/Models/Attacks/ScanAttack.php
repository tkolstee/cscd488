<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class DosAttack extends Attack {

    public $_name                   = "DoS";
    public $_class_name             = "Dos";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "Dos";
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 5;
    public $_initial_analysis_risk  = 4;
    public $_initial_attribution_risk = 2;
    public $_initial_energy_cost    = 200;
    public $_initial_reputation_loss= -100;
    public $possible                = true;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
