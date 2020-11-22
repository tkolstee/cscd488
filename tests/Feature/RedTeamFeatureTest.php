<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Asset;
use Tests\TestCase;

class RedTeamFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanViewRedTeamPages()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/redteam/home');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/redteam/status');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/redteam/store');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/redteam/attacks');
        $response->assertStatus(200);
    }

    public function testUserCanCreateRedTeam()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/redteam/create', [
            'name' => 'redteamname',
        ]);
        $response->assertViewIs('redteam.home');
        $response->assertSee('redteamname');
    }

    public function testRedTeamHomePageDisplaysTeamInfo()
    {
        $team = Team::factory()->red()->create();
        $user = User::factory()->create([
            'redteam' => $team->id,
        ]);
        $response = $this->actingAs($user)->get('redteam/home');
        $response->assertViewIs('redteam.home');
        $response->assertSee([$team->name, $team->balance]);
    }

    public function testRedTeamCanViewAssetsInStore()
    {
        $asset = Asset::factory()->red()->create();
        $team = Team::factory()->red()->create();
        $user = User::factory()->create([
            'redteam' => $team->id,
        ]);
        $response = $this->actingAs($user)->get('redteam/store');
        $response->assertViewIs('redteam.store');
        $response->assertSee($asset->name);
    }

    public function testRedTeamCanBuyAssets()
    {
        $asset = Asset::factory()->red()->create();
        $team = Team::factory()->red()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'redteam' => $team->id,
        ]);
        $expectedBalance = $team->balance - $asset->purchase_cost;
        $response = $this->actingAs($user)->post('/redteam/buy', [
            'results' => [$asset->name],
        ]);
        $response->assertViewIs('redteam.store');
        $response->assertSee('Cash: ' . $expectedBalance);
    }

    public function testRedTeamCannotBuyWithNoMoney()
    {
        $asset = Asset::factory()->red()->create();
        $team = Team::factory()->red()->create([
            'balance' => 0,
        ]);
        $user = User::factory()->create([
            'redteam' => $team->id,
        ]);
        $expectedBalance = $team->balance;
        $response = $this->actingAs($user)->post('/redteam/buy', [
            'results' => [$asset->name],
        ]);
        $response->assertViewIs('redteam.store');
        $response->assertSee('Cash: ' . $expectedBalance);
    }
}
