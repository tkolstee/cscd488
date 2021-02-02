<?php 

namespace App\Models\Payloads;

use App\Models\Team;
use App\Models\Payload;

class WebsiteDefacement extends Payload 
{

    public $_name = "Website Defacement";
    public $_class_name = "WebsiteDefacement";
    public $_tags = ['DBAttack','EndpointExecutable'];
    public $_percentRepLost = 20;

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);

        //Lose 20% per-tern revenue
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['RevenueDeduction'];
        $bonus->percentRevDeducted = 20;
        $bonus->save();
    }
}
