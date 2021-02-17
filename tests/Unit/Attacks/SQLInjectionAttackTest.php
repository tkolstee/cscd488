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
use Illuminate\Support\Facades\Schema;

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

    public function testSqlServerGameSetUp() {
        $controller = new AttackController;
        $controller->sqlSetUp();
        $this->assertTrue(Schema::connection('sql_minigame')->hasTable('users'));
        $this->assertTrue(Schema::connection('sql_minigame')->hasTable('products'));
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

    public function testSqlInjectionAndVpn(){
        $attack = $this->createAttackAndTeams();
        $sqldatabase = new SQLDatabaseAsset;
        Inventory::factory()->create(['team_id' => $attack->blueteam, 'asset_name' => $sqldatabase->class_name]);
        $vpn = new VPNAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $vpn->class_name]);
        $expected = $attack;
        $expected->possible = true;
        $expected->success_chance = 0.8;
        $attack->onPreAttack();
        $attack = Attack::get($attack->class_name, $attack->redteam, $attack->blueteam);
        $this->assertEquals($expected->possible, $attack->possible);
        $this->assertEquals($expected->success_chance, $attack->success_chance);
    }

    public function testMinigameDifficultyFiveAlwaysFails(){
        $attack = $this->createAttackAndTeams();
        $attack->success_chance = 0;
        Attack::updateAttack($attack);
        $attack->onPreAttack();
        $sqldatabase = new SQLDatabaseAsset;
        Inventory::factory()->create(['team_id' => $attack->blueteam, 'asset_name' => $sqldatabase->class_name]);
        $controller = new AttackController;
        $request = Request::create('POST','attack/sqlinjection',[
            'attID' => $attack->id,
            'url' => "",
        ]);
        $response = $controller->sqlInjection($request);
        $attackAfter = Attack::find(1);
        $this->assertEquals($attack->name, $attackAfter->name);
        $this->assertEquals($attack->success_chance, $attackAfter->success_chance);
        $this->assertEquals(0, $attackAfter->success);
    }
}
