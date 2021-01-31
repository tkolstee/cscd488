<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class ImplantedHwServerAttack extends Attack {

    public $_name                   = "Implanted Hardware - Server";
    public $_class_name             = "ImplantedHwServer";
    public $_tags                   = ['HardwareAttack','PhysicalAttack'];
    public $_prereqs                = ['DataCenterAccess'];
    public $_payload_tag           = 'ServerHW';
    public $_initial_difficulty     = 4.5;
    public $_initial_detection_risk = 2;
    public $_initial_analysis_risk  = 2;
    public $_initial_attribution_risk = 2;
    public $_initial_energy_cost    = 500;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
