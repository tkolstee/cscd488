<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Team;
use App\Models\Asset;
use Tests\TestCase;

class BlueTeamFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanViewBlueTeamPages()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/blueteam/home');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/blueteam/status');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/blueteam/store');
        $response->assertStatus(200);
    }

    public function testUserCanCreateBlueTeam()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/blueteam/create', [
            'name' => 'blueteamname',
        ]);
        $response->assertViewIs('blueteam.home');
        $response->assertSee('blueteamname');
    }

    public function testUserCanJoinBlueTeam()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/blueteam/join', [
            'result' => $team->name,
        ]);
        $response->assertViewIs('blueteam.join');
        $response->assertSee($team->name);
    }

    public function testBlueTeamHomePageDisplaysTeamInfo()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $response = $this->actingAs($user)->get('/blueteam/home');
        $response->assertViewIs('blueteam.home');
        $response->assertSee([$team->name, $team->balance]);
    }

    public function testUserCanViewAssetsInStore()
    {
        $asset = Asset::factory()->create();
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $response = $this->actingAs($user)->get('/blueteam/store');
        $response->assertViewIs('blueteam.store');
        $response->assertSee($asset->name);
    }

    public function testUserCanBuyAssets()
    {
        $asset = Asset::factory()->create();
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $expectedBalance = ($team->balance) - ($asset->purchase_cost);
        $response = $this->actingAs($user)->post('/blueteam/buy', [
            'result' => [$asset->name],
        ]);

        $response->assertViewIs('blueteam.store');
        //$response->assertSee($expectedBalance);
    }

    public function testUserCannotBuyWithNoMoney()
    {
        $asset = Asset::factory()->create();
        $team = Team::factory()->create([
            'balance' => 0,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $expectedBalance = $team->balance;
        $response = $this->actingAs($user)->post('/blueteam/buy', [
            'result' => [$asset->name],
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSee($expectedBalance);
    }
}
