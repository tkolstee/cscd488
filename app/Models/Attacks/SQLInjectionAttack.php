<?php

namespace App\Models\Attacks;

use App\Interfaces\AttackHandler;
use App\Models\Attack;

class SQLInjectionAttack extends Attack implements AttackHandler
{
    public function onPreAttack()
    {
        parent::onPreAttack();
    }

    public static function directory(){
        return dirname(__FILE__);
    }
}
