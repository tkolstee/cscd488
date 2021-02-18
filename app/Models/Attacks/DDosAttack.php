<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class DDosAttack extends Attack {

    public $_name                   = "DDoS";
    public $_class_name             = "DDos";
    public $_tags                   = [];
    public $_prereqs                = ['BotNet'];
    public $_payload_choice           = "Dos";
    public $_initial_success_chance = 0.75;
    public $_initial_detection_chance = 1;
    public $_initial_analysis_chance  = 0.9;
    public $_initial_attribution_chance = 0.2;
    public $_initial_energy_cost    = 400;
    public $_help_text              = "Like DoS, but involving hundreds of servers. Hard to recover from.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
