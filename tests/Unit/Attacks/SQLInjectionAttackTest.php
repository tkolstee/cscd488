<?php

namespace Tests\Attacks\Unit;

use Illuminate\Http\Request;
use App\Models\Assets\VPNAsset;
use App\Models\Assets\SQLDatabaseAsset;
use App\Models\Attacks\SQLInjectionAttack;
use App\Http\Controllers\AttackController;
use App\Models\Attack;
use App\Models\User;
use App\Models\Inventory;
use Tests\TestCase;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SQLInjectionAttackTest extends TestCase {
    use RefreshDatabase;

    private function createAttackAndTeams() {
        $user = User::factory()->create();
        $this->be($user);
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $user->blueteam = $blue->id;
        $user->update();
        $sqlAttack = new SQLInjectionAttack;
        return Attack::create($sqlAttack->class_name, $red->id, $blue->id);
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

    public function testMinigameDifficulty1(){
        $attack = $this->createAttackAndTeams();
        $attack->difficulty = 1;
        Attack::updateAttack($attack);
        $attack->onPreAttack();
        $sqldatabase = new SQLDatabaseAsset;
        Inventory::factory()->create(['team_id' => $attack->blueteam, 'asset_name' => $sqldatabase->class_name]);
        $controller = new AttackController;
        $request = Request::create('POST','attack/sqlinjection',[
            'attackName' => $attack->class_name,
            'red' => $attack->redteam,
            'blue' => $attack->blueteam,
            'url' => "",
        ]);
        $response = $controller->sqlInjection($request);
        $this->assertEquals("Success: true", $response->attMsg);
    }
}
