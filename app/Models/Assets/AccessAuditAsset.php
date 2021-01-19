<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;
use App\Models\Team;

class AccessAuditAsset extends Asset {

    public $_name    = "Access Control Audit";
    public $_class_name = "AccessAudit";
    public $_tags    = ['Action'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 500;
    public $_ownership_cost = 0;

    function onPreAttack($attack) {
        
    }

    function action($blueteam){
        $tokens = $blueteam->getTokensByBlue();
        $this->tags = [0];
        foreach($tokens as $token){
            $int = rand(1,10);
            if($int > 4){
                $this->tags[0]++;
                if($int > 8){
                    $this->tags[] = Team::find($token->team_id)->name;
                }
            }
        }
    }

}
