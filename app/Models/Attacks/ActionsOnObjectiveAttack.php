<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ActionsOnObjectiveAttack extends Attack {

    public $_name                   = "Actions on Objectives";
    public $_class_name             = "ActionsOnObjective";
    public $_tags                   = [];
    public $_prereqs                = ['PwnedAccess'];
    public $_payload_choice         = 'StealRevenue';
    public $_initial_success_chance = 0.90;
    public $_initial_detection_chance = 0.8;
    public $_initial_analysis_chance  = 0.2;
    public $_initial_attribution_chance = 0.3;
    public $_initial_energy_cost    = 10;
    public $_help_text              = "Leverage full access to steal money directly.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
