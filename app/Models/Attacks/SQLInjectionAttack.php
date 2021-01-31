<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class SQLInjectionAttack extends Attack {

    public $_name                   = "SQL Injection";
    public $_class_name             = "SQLInjection";
    public $_tags                   = [];
    public $_prereqs                = ['SQLDatabase'];
    public $_payload_tag            = 'DBAttack';
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 3;
    public $_initial_analysis_risk = 3;
    public $_initial_attribution_risk = 1.5;
    public $_initial_energy_cost    = 200;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
