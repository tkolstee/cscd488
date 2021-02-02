<?php

namespace App\Models\Payloads;

use App\Models\Attack;
use App\Models\Payload;

class Ransomware extends Payload 
{

    public $_name    = "Ransomware";
    public $_class_name = "Ransomware";
    public $_tags = ['EndpointExecutable'];

    public function onAttackComplete($attack){
        parent::onAttackComplete($attack);

        //Ransomware always discovered, ensure det_lvl is at least 2
        if ($attack->detection_level < 2) {
            $attack->detection_level = 2;
            Attack::updateAttack($attack);
        }

        //Lose 50% of per turn revenue, decreasing 5% each turn. 
        //10% chance to remove automatically each turn. Can pay to remove.
        $bonus = parent::createBonus($attack);
        $bonus->tags = ['RevenueDeduction', 'ChanceToRemove', 'PayToRemove'];
        $bonus->percentRevDeducted = 50;
        $bonus->removalChance = 10;
        $bonus->removalCostFactor = 2;
        $bonus->save();
    }
}
