<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class PhishingCredentialsAttack extends Attack {

    public $_name                   = "Phishing - Credentials";
    public $_class_name             = "PhishingCredentials";
    public $_tags                   = ['RequiresUserAction'];
    public $_prereqs                = ['FakeLoginSite'];
    public $_payload_choice           = 'BasicAccess';
    public $_initial_difficulty     = 3.5;
    public $_initial_detection_risk = 0.5;
    public $_initial_analysis_risk  = 4;
    public $_initial_attribution_risk = 1;
    public $_initial_energy_cost    = 100;
    public $_help_text              = "Get employee to use a fake login page.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
