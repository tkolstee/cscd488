<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class MitMAttack extends Attack {

    public $_name                   = "MitM";
    public $_class_name             = "MitM";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "BasicAccess";
    public $_initial_success_chance = 0.3;
    public $_initial_detection_chance = 0.2;
    public $_initial_analysis_chance  = 0.7;
    public $_initial_attribution_chance = 0.1;
    public $_initial_energy_cost    = 50;
    public $_help_text              = "Be a middleman for traffic between employees and the company.";

    public $learn_page              = true;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
