<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class DriveByAttack extends Attack {

    public $_name                   = "Drive-By";
    public $_class_name             = "DriveBy";
    public $_tags                   = ['CodeExecution','TargetsEndpoints'];
    public $_prereqs                = ['MaliciousWebsite'];
    public $_payload_tag           = 'EndpointExecutable';
    public $_initial_difficulty     = 2.5;
    public $_initial_detection_risk = 1.5;
    public $_initial_analysis_risk  = 2;
    public $_initial_attribution_risk = 2.5;
    public $_initial_energy_cost    = 100;
    public $_help_text              = "Redirect employees to malicious website. Compromise browser or workstation.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
