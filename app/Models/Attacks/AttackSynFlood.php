<?php

use App\Models\Team;
use DB;

class AttackSynFlood extends Attack {

    public $_name                   = "SYN Flood";
    public $_tags                   = ['ExternalNetworkProtocol', 'DenialOfService'];
    public $_prereqs                = [];
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
        $blueteam->save();
        $redteam->save();

    }

    function onPreAttack() {
        parent::onPreAttack();
    }

}
