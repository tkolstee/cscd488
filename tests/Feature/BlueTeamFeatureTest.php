<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Game;
use Tests\TestCase;

class BlueTeamFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
    }

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
        //$this->assertEquals(1,$response->content());
        $response->assertViewIs('blueteam.home');
        $response->assertSee('blueteamname');
    }

    public function testUserCanJoinBlueTeam()
    {
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'leader' => 1,
            'blueteam' => $team->id,
        ]);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/blueteam/join', [
            'result' => $team->name,
        ]);
        $response->assertViewIs('blueteam.home');
        $response->assertSee([$team->name, $team->balance, $leaderUser->name, $user->name]);
    }

    public function testBlueTeamHomePageDisplaysTeamInfo()
    {
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $response = $this->actingAs($leaderUser)->get('/blueteam/home');
        $response->assertViewIs('blueteam.home');
        $response->assertSee([$team->name, $team->balance, $leaderUser->name]);
    }

    public function testBlueTeamCanViewAssetsInStore()
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

    public function testBlueTeamCanBuyAssets()
    {
        $asset = Asset::factory()->create();
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $response = $this->actingAs($user)->post('/blueteam/buy', [
            'results' => [$asset->name],
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSee($asset->name);
    }

    public function testBlueTeamCannotBuyWithNoMoney()
    {
        $asset = Asset::factory()->create();
        $team = Team::factory()->create([
            'balance' => 0,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $response = $this->actingAs($user)->post('/blueteam/buy', [
            'results' => [$asset->name],
        ]);
        $response->assertViewIs('blueteam.store');
    }
}
