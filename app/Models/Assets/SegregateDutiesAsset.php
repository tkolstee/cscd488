<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;
use App\Models\Team;

class SegregateDutiesAsset extends Asset 
{

    public $_name    = "Segregation of Duties Policy";
    public $_class_name = "SegregateDuties";
    public $_tags    = ['AddToken'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 300;
    public $_ownership_cost = 50;
    public $_description = "Separates duties of those in charge so neither can see the whole picture. 
        Internal attacks require an extra AccessToken to carry out.";

    public function onPreAttack($attack)
    {
        
    }
}
