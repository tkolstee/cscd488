<?php

namespace App\Models\Assets;

use App\Interfaces\AttackHandler;
use App\Models\Asset;
use App\Models\Attack;

class SQLDatabaseAsset extends Asset implements AttackHandler
{
    public function onPreAttack($attackLog)
    {
        $attack = Attack::find($attackLog->attack_id);
        //Test for now! attack will succeed if blue team has a sqldatabase
        if ($attack->name == "SQLInjection") {
            $attackLog->possible = true;
        }
        return $attackLog;
    }
}
