<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class DDosAttack extends Attack {

    public $_name                   = "DDoS";
    public $_class_name             = "DDos";
    public $_tags                   = [];
    public $_prereqs                = ['BotNet'];
    public $_payload_choice           = "Dos";
    public $_initial_difficulty     = 1.25;
    public $_initial_detection_risk = 5;
    public $_initial_analysis_risk  = 4.5;
    public $_initial_attribution_risk = 1;
    public $_initial_energy_cost    = 200;

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
