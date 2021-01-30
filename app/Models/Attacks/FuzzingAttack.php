<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class FuzzingAttack extends Attack {

    public $_name                   = "Fuzzing";
    public $_class_name             = "Fuzzing";
    public $_tags                   = [];
    public $_prereqs                = ['ExternalNetworkService'];
    public $_payload_choice         = 'Fuzzing';
    public $_initial_difficulty     = 3.5;
    public $_initial_detection_risk = 3;
    public $_initial_analysis_risk  = 1;
    public $_initial_attribution_risk = 0.5;
    public $_initial_energy_cost    = 200;
    public $_initial_reputation_loss= -100;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
