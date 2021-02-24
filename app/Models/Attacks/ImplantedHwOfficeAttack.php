<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ImplantedHwOfficeAttack extends Attack {

    public $_name                   = "Implanted Hardware - Office";
    public $_class_name             = "ImplantedHwOffice";
    public $_tags                   = ['HardwareAttack','PhysicalAttack'];
    public $_prereqs                = ['PhysicalAccess'];
    public $_payload_tag           = 'OfficeHW';
    public $_initial_success_chance = 0.1;
    public $_initial_detection_chance = 0.25;
    public $_initial_analysis_chance = 0.4;
    public $_initial_attribution_chance = 0.3;
    public $_initial_energy_cost    = 300;
    public $_help_text              = "Plant a piece of hardware in the office.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
