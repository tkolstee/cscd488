<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class PasswordGuessingAttack extends Attack {

    public $_name                   = "Password Guessing";
    public $_class_name             = "PasswordGuessing";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "BasicAccess";
    public $_initial_success_chance = 0.30;
    public $_initial_detection_chance = 0.7;
    public $_initial_analysis_chance  = 0.9;
    public $_initial_attribution_chance = 0.1;
    public $_initial_energy_cost    = 300;
    public $_help_text              = "Gain access through brute force and dictionary attacks.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
