<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class OsintAttack extends Attack {

    public $_name                   = "OSINT";
    public $_class_name             = "Osint";
    public $_tags                   = [];
    public $_prereqs                = [];
    public $_payload_choice           = "SecInfo";
    public $_initial_success_chance = 0.8;
    public $_initial_detection_chance = 0;
    public $_initial_analysis_chance  = 0;
    public $_initial_attribution_chance = 0;
    public $_initial_energy_cost    = 30;
    public $_help_text              = "Recon from public information.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
