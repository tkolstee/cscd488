<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class FoundMediaAttack extends Attack {

    public $_name                   = "\"Found\" Media";
    public $_class_name             = "FoundMedia";
    public $_tags                   = ['RequiresUserAction','TargetsEndpoints','CodeExecution'];
    public $_prereqs                = [];
    public $_payload_tag            = 'EndpointExecutable';
    public $_initial_difficulty     = 3.5;
    public $_initial_detection_risk = 2;
    public $_initial_analysis_risk  = 2;
    public $_initial_attribution_risk = 2.5;
    public $_initial_energy_cost    = 200;
    public $_help_text              = "An employee \"finds\" a flash drive and inserts it.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
