<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class PasswordGuessingAttack extends Attack {

    public $_name                   = "Password Guessing";
    public $_class_name             = "PasswordGuessing";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "BasicAccess";
    public $_initial_difficulty     = 3.5;
    public $_initial_detection_risk = 3.5;
    public $_initial_analysis_risk  = 4.5;
    public $_initial_attribution_risk = 0.5;
    public $_initial_energy_cost    = 300;

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
