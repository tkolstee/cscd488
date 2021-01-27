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
    public $_initial_detection_risk = 5;
    public $_initial_energy_cost    = 100;
    public $_initial_blue_loss      = -50;
    public $_initial_red_gain       = 100;
    public $_initial_reputation_loss= -100;
    public $possible                = true;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
