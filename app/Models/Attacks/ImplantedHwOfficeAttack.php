<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ImplantedHwOfficeAttack extends Attack {

    public $_name                   = "Implanted Hardware - Office";
    public $_class_name             = "ImplantedHwOffice";
    public $_tags                   = ['HardwareAttack','PhysicalAttack'];
    public $_prereqs                = ['PhysicalAccess'];
    public $_payload_tag           = 'OfficeHW';
    public $_initial_difficulty     = 4.5;
    public $_initial_detection_risk = 1.25;
    public $_initial_analysis_risk  = 2;
    public $_initial_attribution_risk = 1.5;
    public $_initial_energy_cost    = 300;
    public $_initial_reputation_loss= -100;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
