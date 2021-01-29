<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class MaliciousInsiderAttack extends Attack {

    public $_name                   = "Malicious Insider";
    public $_class_name             = "MaliciousInsider";
    public $_tags                   = ['PhysicalAttack'];
    public $_prereqs                = [];
    public $_payload_choice         = 'MaliciousInsider';
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 2.5;
    public $_initial_analysis_risk  = 5;
    public $_initial_attribution_risk = 4;
    public $_initial_energy_cost    = 1000;
    public $_initial_reputation_loss= -100;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
