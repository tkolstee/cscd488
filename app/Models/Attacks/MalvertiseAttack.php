<?php

namespace App\Models\Attacks;

use App\Models\Attack;


class MalvertiseAttack extends Attack {

    public $_name                   = "Malvertise";
    public $_class_name             = "Malvertise";
    public $_tags                   = [];
    public $_prereqs                = ['AdDept'];
    public $_initial_success_chance = 3;
    public $_initial_detection_chance = 4;
    public $_initial_energy_cost    = 100;

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
