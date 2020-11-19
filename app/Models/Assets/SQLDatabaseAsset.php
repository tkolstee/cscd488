<?php

namespace App\Models\Assets;

use App\Interfaces\AttackHandler;
use App\Models\Asset;

class SQLDatabaseAsset extends Asset implements AttackHandler
{
    public function onPreAttack($attackLog)
    {
        //Not sure yet! do nothing
        return $attackLog;
    }
}
