<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ImplantedHwServerAttack extends Attack {

    public $_name                   = "Implanted Hardware - Server";
    public $_class_name             = "ImplantedHwServer";
    public $_tags                   = ['HardwareAttack','PhysicalAttack'];
    public $_prereqs                = ['DataCenterAccess'];
    public $_payload_tag           = 'ServerHW';
    public $_initial_success_chance = 4.5;
    public $_initial_detection_chance = 2;
    public $_initial_analysis_chance  = 2;
    public $_initial_attribution_chance = 2;
    public $_initial_energy_cost    = 500;
    public $_help_text              = "Plant a piece of hardware in the datacenter.";

    public $learn_page              = false;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
