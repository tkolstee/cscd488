<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class PhishingLinkAttack extends Attack {

    public $_name                   = "Phishing - Link";
    public $_class_name             = "PhishingLink";
    public $_tags                   = ['RequiresUserAction','TargetsEndpoints','CodeExecution'];
    public $_prereqs                = ['MaliciousWebsite'];
    public $_payload_tag          = 'EndpointExecutable';
    public $_initial_success_chance = 0.3;
    public $_initial_detection_chance = 0.2;
    public $_initial_analysis_chance  = 0.5;
    public $_initial_attribution_chance = 0.5;
    public $_initial_energy_cost    = 150;
    public $_help_text              = "Get an employee to click on a malicious link.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
