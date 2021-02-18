<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SQLInjectionAttack extends Attack {

    public $_name                   = "SQL Injection";
    public $_class_name             = "SQLInjection";
    public $_tags                   = [];
    public $_prereqs                = ['SQLDatabase'];
    public $_payload_tag            = 'DBAttack';
    public $_initial_success_chance = 0.6;
    public $_initial_detection_chance = 0.6;
    public $_initial_analysis_chance = 0.6;
    public $_initial_attribution_chance = 0.3;
    public $_initial_energy_cost    = 200;
    public $_help_text              = "Send commands to the company's database.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
