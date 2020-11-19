<?php

namespace App\Models;

use App\Interfaces\AttackHandler;

class SQLDatabaseAsset extends Asset implements AttackHandler
{
    public function onPreAttack($attackLog)
    {
        //Not sure yet! do nothing
        return $attackLog;
    }
}