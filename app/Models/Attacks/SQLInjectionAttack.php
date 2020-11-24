<?php

namespace App\Models\Attacks;

use App\Models\Attack;
use App\Models\Team;


class SQLInjectionAttack extends Attack {

    public $_name                   = "SQLInjection";
    public $_tags                   = [];
    public $_prereqs                = ['SQLDatabase'];
    public $_initial_difficulty     = 2;
    public $_initial_detection_risk = 5;
    public $_initial_energy_cost    = 100;
    public $possible                = true;


    function onAttackComplete() {

        parent::onAttackComplete();

        $blueteam = Team::find($this->blueteam);
        $redteam  = Team::find($this->redteam);

        if ( $this->success ) {
            $blueteam->balance -= 50;
        }
        if ( $this->detected ) {
            $redteam->reputation -= 100;
        }
        $redteam->energy -= $this->energy_cost;
        $blueteam->update();
        $redteam->update();

    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
