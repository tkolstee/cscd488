<?php

namespace App\Models\Assets;

use App\Interfaces\AttackHandler;
use App\Models\Asset;
use App\Models\Attack;

class SQLDatabaseAsset extends Asset implements AttackHandler
{
    public function onPreAttack($attack)
    {
        
    }
}
