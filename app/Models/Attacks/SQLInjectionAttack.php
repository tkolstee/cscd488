<?php

namespace App\Models\Attacks;

use App\Interfaces\AttackHandler;
use App\Models\Attack;

class SQLInjectionAttack extends Attack implements AttackHandler
{
    public function onPreAttack($attackLog)
    {
        //Not sure yet! do nothing
        return $attackLog;
    }
}
