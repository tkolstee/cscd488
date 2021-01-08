<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Game;
use Tests\TestCase;
use App\Models\Inventory;

class BlueTeamFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $user = User::factory()->create();
        $this->be($user);
    }

    public function testUserCanViewBlueTeamPages()
    {
        $response = $this->get('/blueteam/home');
        $response->assertStatus(200);
        $response = $this->get('/blueteam/status');
        $response->assertStatus(200);
        $response = $this->get('/blueteam/store');
        $response->assertStatus(200);
    }

    public function testUserCanCreateBlueTeam()
    {
        $response = $this->post('/blueteam/create', [
            'name' => 'blueteamname',
        ]);
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
        $response->assertSee([$team->name, $team->balance, $leaderUser->username, $user->username]);
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
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $response = $this->actingAs($user)->get('/blueteam/store');
        $response->assertViewIs('blueteam.store');
        $response->assertSee("Firewall");
    }

    public function testBlueTeamCanAddToCart()
    {
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $response = $this->actingAs($user)->post('/blueteam/buy', [
            'results' => ["Firewall"],
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSee("Firewall");
    }

    public function testBlueTeamCanBuyAssets()
    {
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $this->actingAs($user)->post('/blueteam/buy', [
            'results' => ["Firewall"],
        ]);
        $expectedBalance = $team->balance - Asset::get("Firewall")->purchase_cost;
        $response = $this->actingAs($user)->get('/blueteam/endturn');
        $response->assertViewIs('blueteam.home');
        $response->assertSee('Revenue: ' . $expectedBalance);
    }

    public function testBlueTeamCanViewAssetsInInventory()
    {
        $asset = Asset::getBuyableBlue()[0];
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $this->be($user);
        $response = $this->get('/blueteam/inventory');
        $response->assertSee("You have no assets.");

        Inventory::factory()->create([
            'asset_name' => $asset->name,
            'team_id' => $team->id,
            'quantity' => 5,
        ]);
        $response = $this->get('/blueteam/inventory');
        $response->assertSeeInOrder([$asset->name, "5"]);
    }
}
