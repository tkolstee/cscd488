<?php

namespace Tests\Attacks\Unit;

use App\Models\Assets\SQLDatabaseAsset;
use App\Models\Attacks\SQLInjectionAttack;
use App\Models\Attack;
use App\Models\Inventory;
use Tests\TestCase;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SQLInjectionAttackTest extends TestCase {
    use RefreshDatabase;

    public function createAttackAndTeams() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $sqlAttack = new SQLInjectionAttack;
        return Attack::create($sqlAttack->_class_name, $red->id, $blue->id);
    }

    public function testSqlInjectionNoAssets() {
        $attack = $this->createAttackAndTeams();
        
        $expected = $attack;
        $expected->possible = false;
        $attack->onPreAttack();
        $this->assertEquals($expected, $attack);
    }

    public function testSqlInjectionAndDatabase() {
        $attack = $this->createAttackAndTeams();
        $sqldatabase = new SQLDatabaseAsset;
        Inventory::factory()->create(['team_id' => $attack->blueteam, 'asset_name' => $sqldatabase->class_name]);

        $expected = $attack;
        $expected->possible = true;
        $attack->onPreAttack();
        $this->assertEquals($expected, $attack);
    }
}
