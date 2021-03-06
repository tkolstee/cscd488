<?php

namespace App\Models\Attacks;

use App\Models\Attack;


class MalvertiseAttack extends Attack {

    public $_name                   = "Malvertise";
    public $_class_name             = "Malvertise";
    public $_tags                   = [];
    public $_prereqs                = ['AdDept'];
    public $_initial_success_chance = 0.4;
    public $_initial_detection_chance = 0.8;
    public $_initial_energy_cost    = 100;
    public $_help_text              = "Infect a victim's machine with malware through an advertisement.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
