<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Game;
use App\Models\Bonus;
use App\Models\Attack;
use Tests\TestCase;
use Auth;

class RedMiscFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $team = Team::factory()->red()->create();
        $user = User::factory()->create([
            'redteam' => $team->id,
        ]);
        $this->be($user);
    }

    public function testUserCanViewRedTeamPages()
    {
        $response = $this->get('/redteam/home');
        $response->assertStatus(200);
        $response = $this->get('/redteam/status');
        $response->assertStatus(200);
        $response = $this->get('/redteam/store');
        $response->assertStatus(200);
        $response = $this->get('/redteam/inventory');
        $response->assertStatus(200);
        $response = $this->get('/redteam/attacks');
        $response->assertStatus(200);
    }

    public function testUserCanCreateRedTeam()
    {
        $user = Auth::user();
        $user->redteam = null;
        $user->update();
        $response = $this->post('/redteam/create', [
            'name' => 'redteamname',
        ]);
        $response->assertViewIs('redteam.home');
        $response->assertSee('redteamname');
    }

    public function testRedHomePageDisplaysTeamInfo()
    {
        $team = Auth::user()->getRedTeam();
        $response = $this->get('redteam/home');
        $response->assertViewIs('redteam.home');
        $response->assertSee([$team->name, $team->balance]);
        $response->assertSee("Start Attack");
    }

    //Settings Tests

    public function testBlueTeamSettings(){
        $team = Auth::user()->getRedTeam();
        $response = $this->get('/redteam/settings');
        $response->assertViewIs('redteam.settings');
        $response->assertSeeInOrder([$team->name, Auth::user()->username, $team->balance,]);
        //ChangeName
        $response = $this->post('/redteam/settings', [
            'changeNameBtn' => 1,
        ]);
        $response->assertViewIs('redteam.settings');
        $response->assertSee("changeNameSubmit");
        //LeaveTeam
        $response = $this->post('/redteam/settings', [
            'leaveTeamBtn' => 1,
        ]);
        $response->assertViewIs('redteam.settings');
        $response->assertSee("leaveAndStayOnTeam");
    }

    public function testChangeName(){
        $team = Auth::user()->getRedTeam();
        $response = $this->post('/redteam/changename', [
            'name' => "NewName",
        ]);
        $response->assertViewIs('redteam.settings');
        $response->assertSee("NewName");
        $response->assertDontSee($team->name);
        $team2 = Team::factory()->red()->create([
            'name' => "NameTaken"
        ]);
        $response = $this->post('/redteam/changename', [
            'name' => "NameTaken",
        ]);
        $response->assertViewIs('redteam.settings');
        $response->assertSee(["name-taken", "NewName"]);
        $response->assertDontSee("NameTaken");
    }

    
    public function testLeaveTeam(){
        $team = Auth::user()->getRedTeam();
        $response = $this->post('/redteam/leaveteam', [
            'result' => "invalid",
        ]);
        $response->assertViewIs('redteam.settings');
        $response->assertSee("invalid-option");
        $response = $this->post('/redteam/leaveteam', [
            'result' => "stay",
        ]);
        $response->assertViewIs('redteam.settings');
        $response = $this->post('/redteam/leaveteam', [
            'result' => "leave",
        ]);
        $response->assertViewIs('redteam.home');
        $response->assertDontSee($team->name);
    }

    //Status tests

    public function testStatusDisplaysBonuses(){
        $red = Auth::user()->getRedTeam();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack->detection_level = 1;
        Attack::updateAttack($attack);
        $bonus = Bonus::createBonus($red->id, []);
        $bonus->target_id = $blue->id;
        $bonus->attack_id = $attack->id;
        $bonus->payload_name = "SQLPayload";
        $bonus->update();
        $response = $this->get('/redteam/status');
        $response->assertViewIs('redteam.status');
        $response->assertSeeInOrder([
           $bonus->payload_name, $blue->name
        ]);
    }

}