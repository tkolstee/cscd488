<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class MitMAttack extends Attack {

    public $_name                   = "MitM";
    public $_class_name             = "MitM";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "BasicAccess";
    public $_initial_difficulty     = 3;
    public $_initial_detection_risk = 2;
    public $_initial_analysis_risk  = 4;
    public $_initial_attribution_risk = 2;
    public $_initial_energy_cost    = 50;
    public $_initial_reputation_loss= -100;
    public $possible                = true;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
