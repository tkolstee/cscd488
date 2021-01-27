<?php

namespace App\Models\Attacks;

use App\Models\Attack;

class PhishingAttachmentAttack extends Attack {

    public $_name                   = "Phishing - Attachment";
    public $_class_name             = "PhishingAttachment";
    public $_tags                   = ['RequiresUserAction','TargetsEndpoints','CodeExecution'];
    public $_prereqs                = [];
    public $_payload_tag            = 'EndpointExecutable';
    public $_initial_difficulty     = 4;
    public $_initial_detection_risk = 2;
    public $_initial_analysis_risk  = 3;
    public $_initial_attribution_risk = 2;
    public $_initial_energy_cost    = 50;
    public $_initial_reputation_loss= -100;
    public $possible                = true;


    function onAttackComplete() {
        parent::onAttackComplete();
    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
