<?php

namespace App\Models\Assets;

use App\Models\Asset;
use App\Models\Attack;

class SQLDatabaseAsset extends Asset 
{

    public $_name    = "SQL Database";
    public $_class_name = "SQLDatabase";
    public $_tags    = [];
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 500;
    public $_ownership_cost = -1000;
    public $_description = "Database to store information. Generates revenue but is vulnerable to SQL Database attack.";

    public function onPreAttack($attack)
    {
        
    }
}
