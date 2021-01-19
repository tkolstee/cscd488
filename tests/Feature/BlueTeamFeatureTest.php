<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Attack;
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

    public function testBlueTeamCanViewAttackNotificationsLevel2()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
        ]);
        $red = Team::factory()->red()->create();
        Inventory::factory()->create(['team_id' => $blue->id, 'asset_name' => 'Security Analyst']);
        $attack1 = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack1->detection_level = 2;
        $attack1->setNotified(false);
        $attack2 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack2->detection_level = 2;
        $attack2->setNotified(false);

        $response = $this->actingAs($user)->get('/blueteam/home');
        $response->assertSeeInOrder([$attack1->name, $attack2->name]);
    }

    public function testBlueTeamAttackNotificationsLevel1()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(false);

        $response = $this->actingAs($user)->get('/blueteam/home');
        $response->assertSee("Your team was attacked while you were away!");
        $response->assertDontSee($attack1->name);
    }

    public function testBlueTeamCanClearAttackNotifications()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(false);

        $response = $this->actingAs($user)->get('/blueteam/clearNotifs');
        $response->assertViewIs('blueteam.home');
        $response->assertDontSee($attack1->name);
    }

    public function testBlueTeamCanBroadcastAttacks()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(false);
        $attack1 = Attack::find(1);

        $response = $this->actingAs($user)->post('/blueteam/broadcast', [
            'attID' => $attack1->id,
        ]);
        $response->assertViewIs('blueteam.home');
        $response->assertDontSee($attack1->class_name);

        $response = $this->actingAs($user)->get('/blueteam/news');
        $response->assertSeeInOrder([$red->name, $blue->name]); //Check for 'redname attacked bluename' text basically. Change when we add more to news page?
    }

    public function testBlueTeamCanSeeBroadcastButtonHomePage()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);

        $response = $this->get('blueteam/home');
        $response->assertDontSee('Broadcast');

        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(false);
        $attack1 = Attack::find(1);

        $response = $this->get('blueteam/home');
        $response->assertSee('Broadcast');
    }

    public function testBlueTeamCanSeeBroadcastButtonAttacksPage()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);

        $response = $this->get('blueteam/attacks');
        $response->assertDontSee('Broadcast');

        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(true);
        $attack1 = Attack::find(1);

        $response = $this->get('blueteam/attacks');
        $response->assertSee('Broadcast');
    }

    public function testBlueTeamCannotSeeBroadcastOldAttacks()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);

        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(true);
        $attack1 = Attack::find(1);
        $attack1->created_at = $attack1->created_at->subDays(4); //more than 3 days is 'old'
        $attack1->update();
        
        $response = $this->get('blueteam/attacks');
        $response->assertDontSee('Broadcast');
    }

    public function testLeaderboardDisplaysInfo()
    {
        $blue1 = Team::factory()->create(['reputation' => 10000]);
        $blue2 = Team::factory()->create(['reputation' => 500]);
        $blue3 = Team::factory()->create(['reputation' => 100]);
        $user = User::factory()->create([
            'blueteam' => $blue1->id,
            'leader' => 1,
        ]);
        $this->be($user);

        $response = $this->get('blueteam/leaderboard');
        $response->assertSeeInOrder([$blue1->name, $blue1->reputation, 
                                    $blue2->name, $blue2->reputation,
                                    $blue3->name, $blue3->reputation]);
    }
}
