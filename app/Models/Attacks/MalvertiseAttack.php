<?php

namespace App\Models\Attacks;

use App\Models\Attack;


class MalvertiseAttack extends Attack {

    public $_name                   = "Malvertise";
    public $_class_name             = "Malvertise";
    public $_tags                   = [];
    public $_prereqs                = ['AdDept'];
    public $_payloads               = [];
    public $_initial_difficulty     = 3;
    public $_initial_detection_risk = 4;
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
