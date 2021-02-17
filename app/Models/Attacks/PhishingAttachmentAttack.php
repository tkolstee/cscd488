<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class PhishingAttachmentAttack extends Attack {

    public $_name                   = "Phishing - Attachment";
    public $_class_name             = "PhishingAttachment";
    public $_tags                   = ['RequiresUserAction','TargetsEndpoints','CodeExecution'];
    public $_prereqs                = [];
    public $_payload_tag            = 'EndpointExecutable';
    public $_initial_difficulty     = 3.75;
    public $_initial_detection_chance = 2;
    public $_initial_analysis_chance  = 3;
    public $_initial_attribution_chance = 1.5;
    public $_initial_energy_cost    = 50;
    public $_help_text              = "Get an employee to open a malicious attachment.";

    public $learn_page              = true;

    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
