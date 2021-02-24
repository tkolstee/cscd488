<?php

namespace Tests\Attacks\Unit;

use Auth;
use Illuminate\Http\Request;
use App\Models\Assets\AccessTokenAsset;
use App\Models\Attacks\BackdoorPrivilegedAttack;
use App\Models\Attacks\BackdoorPwnedAttack;
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
            case("Privileged"): $backdoor = new BackdoorPrivilegedAttack(); break;
            case("Pwned"): $backdoor = new BackdoorPwnedAttack(); break;
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

    public function testBackdoorPrivilegedNoAssets() {
        $attack = $this->createAttackAndTeams("Privileged");
        $expectedAttack = $attack;
        $attack->onPreAttack();
        $this->assertEquals($expectedAttack, $attack);
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPrivilegedPreLevel1Token() {
        $attack = $this->createAttackAndTeams("Privileged");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        $attack->onPreAttack();
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPrivilegedPreLevel2Token() {
        $attack = $this->createAttackAndTeams("Privileged");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        $attack->onPreAttack();
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPrivilegedPreLevel1And2Tokens() {
        $attack = $this->createAttackAndTeams("Privileged");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        $initial_energy_cost = $attack->energy_cost;
        $attack->onPreAttack();
        $this->assertEquals(true, $attack->possible);
        $this->assertEquals($initial_energy_cost * 2, $attack->energy_cost);
    }

    public function testBackdoorPrivilegedPreAllTokens() {
        $attack = $this->createAttackAndTeams("Privileged");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 3]);
        $initial_energy_cost = $attack->energy_cost;
        $attack->onPreAttack();
        $this->assertEquals(true, $attack->possible);
        $this->assertEquals($initial_energy_cost, $attack->energy_cost);
    }

    public function testBackdoorPrivilegedCompleteFail() {
        $attack = $this->createAttackAndTeams("Privileged");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        $attack->success = false;
        $attack->detected = false;
        $attack->onAttackComplete();
        $this->assertEquals(true, $attack->detected);
    }

    public function testBackdoorPrivilegedCompleteSuccess() {
        $attack = $this->createAttackAndTeams("Privileged");
        $blueteam = Team::find($attack->blueteam);
        $redteam = Team::find($attack->redteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        $attack->success = true;
        $attack->detected = false;
        $attack->onAttackComplete();
        $tokens = $redteam->getTokens()->where('level','=',2);
        $this->assertEquals(2, $tokens->first()->quantity);
    }

    public function testBackdoorPwnedNoAssets() {
        $attack = $this->createAttackAndTeams("Pwned");
        $expectedAttack = $attack;
        $attack->onPreAttack();
        $this->assertEquals($expectedAttack, $attack);
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPwnedPreLevel1Token() {
        $attack = $this->createAttackAndTeams("Pwned");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        $attack->onPreAttack();
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPwnedPreLevel1And2Tokens() {
        $attack = $this->createAttackAndTeams("Pwned");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        $attack->onPreAttack();
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPwnedPreLevel1And3Tokens() {
        $attack = $this->createAttackAndTeams("Pwned");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 3]);
        $attack->onPreAttack();
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPwnedPreLevel2And3Tokens() {
        $attack = $this->createAttackAndTeams("Pwned");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 3]);
        $attack->onPreAttack();
        $this->assertEquals(false, $attack->possible);
    }

    public function testBackdoorPwnedPreAllTokens() {
        $attack = $this->createAttackAndTeams("Pwned");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 3]);
        $attack->onPreAttack();
        $this->assertEquals(1, $attack->errormsg);
        $this->assertEquals(true, $attack->possible);
    }

    public function testBackdoorPwnedCompleteFail() {
        $attack = $this->createAttackAndTeams("Pwned");
        $blueteam = Team::find($attack->blueteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 3]);
        $attack->success = false;
        $attack->detected = false;
        $attack->onAttackComplete();
        $this->assertEquals(true, $attack->detected);
    }

    public function testBackdoorPwnedCompleteSuccess() {
        $attack = $this->createAttackAndTeams("Pwned");
        $blueteam = Team::find($attack->blueteam);
        $redteam = Team::find($attack->redteam);
        $token = new AccessTokenAsset;
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 2]);
        Inventory::factory()->create(['team_id' => $attack->redteam, 'asset_name' => $token->class_name, 'info' => $blueteam->name, 'level' => 3]);
        $attack->success = true;
        $attack->detected = false;
        $attack->onAttackComplete();
        $tokens = $redteam->getTokens()->where('level','=',3);
        $this->assertEquals(2, $tokens->first()->quantity);
    }

}