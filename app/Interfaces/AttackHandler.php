<?php

namespace App\Interfaces;

interface AttackHandler
{
    public function onPreAttack($attackLog);
}
