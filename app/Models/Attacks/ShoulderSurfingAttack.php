<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ShoulderSurfingAttack extends Attack {

    public $_name                   = "Shoulder Surfing";
    public $_class_name             = "ShoulderSurfing";
    public $_tags                   = ['PhysicalAttack'];
    public $_prereqs                = [];
    public $_payload_choice           = "BasicAccess";
    public $_initial_difficulty     = 3;
    public $_initial_detection_risk = 1;
    public $_initial_analysis_risk  = 2.5;
    public $_initial_attribution_risk = 0.5;
    public $_initial_energy_cost    = 30;
    public $_help_text              = "Obtain password via physically watching/recording someone type it in.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
