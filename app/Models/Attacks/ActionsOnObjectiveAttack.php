<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ActionsOnObjectiveAttack extends Attack {

    public $_name                   = "Actions on Objectives";
    public $_class_name             = "ActivesOnObjective";
    public $_tags                   = [];
    public $_prereqs                = ['PwnedAccess'];
    public $_payload_choice         = 'StealRevenue';
    public $_initial_difficulty     = 0.5;
    public $_initial_detection_risk = 4;
    public $_initial_analysis_risk  = 1;
    public $_initial_attribution_risk = 1.5;
    public $_initial_energy_cost    = 10;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
