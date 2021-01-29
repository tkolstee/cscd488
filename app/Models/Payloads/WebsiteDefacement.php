<?php 

namespace App\Models\Payloads;

use App\Models\Team;
use App\Models\Payload;

class WebsiteDefacement extends Payload 
{

    public $_name = "Website Defacement";
    public $_class_name = "WebsiteDefacement";
    public $_tags = ['DBAttack','EndpointExecutable'];

    public function onAttackComplete($attack){
        $bonus = parent::onAttackComplete($attack);

        //Blueteam immediately loses 20% of reputation
        $blueteam = Team::find($attack->blueteam);
        $repLost = $blueteam->reputation * .20 * -1;
        $blueteam->changeReputation($repLost);

        //Lose 20% per-tern revenue
        $bonus->tags = ['RevenueDeduction'];
        $bonus->percentRevDeducted = 20;
        $bonus->save();
    }
}
