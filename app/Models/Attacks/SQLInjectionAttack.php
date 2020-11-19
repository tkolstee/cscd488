<?php

namespace App\Models;

use App\Interfaces\AttackHandler;

class SQLInjectionAttack extends Attack implements AttackHandler
{
    public function onPreAttack($attackLog)
    {
        //Not sure yet! do nothing
        return $attackLog;
    }
}
