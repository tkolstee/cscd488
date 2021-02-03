<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class XssAttack extends Attack {

    public $_name                   = "XSS";
    public $_class_name             = "Xss";
    public $_tags                   = ['TargetsCustomers'];
    public $_prereqs                = ['MaliciousWebsite','BlueWebsite'];
    public $_payload_choice           = "Xss";
    public $_initial_difficulty     = 3.5;
    public $_initial_detection_risk = 1;
    public $_initial_analysis_risk  = 2.5;
    public $_initial_attribution_risk = 2.5;
    public $_initial_energy_cost    = 50;
    public $_help_text              = "Redirect customers to your own website.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
