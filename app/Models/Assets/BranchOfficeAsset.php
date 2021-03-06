<?php

namespace App\Models\Assets;

use App\Models\Asset;

class BranchOfficeAsset extends Asset 
{

    public $_name    = "Branch Office";
    public $_class_name = "BranchOffice";
    public $_tags    = ['ExternalNetworkService'];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 2000;
    public $_ownership_cost = -2500;
    public $_description = "Open a branch office that helps defend against physical attacks.";

    public function onPreAttack($attack)
    {
        if (in_array('PhysicalAttack', $attack->tags)){
            $attack->changeSuccessChance(-.15);
        }
    }
}
