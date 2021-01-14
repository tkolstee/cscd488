<?php

namespace Tests\Attacks\Unit;

use Auth;
use Illuminate\Http\Request;
use App\Models\Assets\AccessTokenAsset;
use App\Models\Attacks\BackdoorBasicAttack;
use App\Http\Controllers\AttackController;
use App\Models\Team;
use App\Models\User;
use App\Models\Attack;
use App\Models\Inventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BackdoorAttackTest extends TestCase {
    use RefreshDatabase;

    public function createAttackAndTeams($level) {
        $user = User::factory()->create();
        $this->be($user);
        $red = Team::factory()->red()->create();
        Auth::user()->redteam = $red->id;
        Auth::user()->update();
        $blue = Team::factory()->create();
        switch($level){
            case("Basic"): $backdoor = new BackdoorBasicAttack(); break;
            default: $backdoor = new BackdoorBasicAttack(); break;
        }
        $attack = Attack::create($backdoor->class_name, $red->id, $blue->id);
        $attack = Attack::get($attack->class_name, $attack->redteam, $attack->blueteam);
        return $attack;
    }

    public function testBackdoorBasicNoAssets() {
        $attack = $this->createAttackAndTeams("Basic");
        $expectedAttack = $attack;
        $attack->onPreAttack();
        $this->assertEquals($expectedAttack, $attack);
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorBasicPreLevel1Token() {
        $attack = $this->createAttackAndTeams("Basic");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        $initial_energy_cost = $attack->energy_cost;
        $attack->onPreAttack();
        $this->assertEquals(true, $attack->possible);
        $this->assertEquals((2 * $initial_energy_cost), $attack->energy_cost);
    }

    public function testBackdoorBasicPreLevel1And2Tokens() {
        $attack = $this->createAttackAndTeams("Basic");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        $initial_energy_cost = $attack->energy_cost;
        $attack->onPreAttack();
        $this->assertEquals(true, $attack->possible);
        $this->assertEquals($initial_energy_cost, $attack->energy_cost);
    }

    public function testBackdoorBasicCompleteFail() {
        $attack = $this->createAttackAndTeams("Basic");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        $attack->success = false;
        $attack->detected = false;
        $attack->onAttackComplete();
        $this->assertEquals(true, $attack->detected);
    }

    public function testBackdoorBasicCompleteSuccess() {
        $attack = $this->createAttackAndTeams("Basic");
        $blueteam = Team::find($attack->blueteam);
        $redteam = Team::find($attack->redteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        $attack->success = true;
        $attack->detected = false;
        $attack->onAttackComplete();
        $tokens = $redteam->getTokens();
        $this->assertEquals(2, $tokens->first()->quantity);
    }

}