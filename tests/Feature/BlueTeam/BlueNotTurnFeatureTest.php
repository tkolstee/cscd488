<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Attack;
use App\Models\Asset;
use App\Models\Bonus;
use App\Models\Game;
use Tests\TestCase;
use App\Models\Inventory;

class BlueNotTurnFeatureTest extends TestCase
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

    //Create and Join tests

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

    public function testUserCanViewMembersBeforeJoin(){
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'leader' => 1,
            'blueteam' => $team->id,
        ]);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/blueteam/joinmembers', [
            'submit' => $team->name,
        ]);
        $response->assertViewIs('blueteam.join');
        $response->assertSee([$team->name,  $leaderUser->username]);
    }

    //Pages available while not on turn

    public function testBlueTeamHomePageDisplaysTeamInfo()
    {
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $leaderUser->setTurnTaken(1);
        $response = $this->actingAs($leaderUser)->get('/blueteam/home');
        $response->assertViewIs('blueteam.home');
        $response->assertSee([$team->name, $team->balance, $leaderUser->name]);
    }

    public function testBlueTeamSettings(){
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 0,
        ]);
        $leaderUser->setTurnTaken(1);
        $response = $this->actingAs($leaderUser)->get('/blueteam/settings');
        $response->assertViewIs('blueteam.settings');
        $response->assertSee([$team->name, $team->balance, $leaderUser->name]);
        //ChangeName
        $response = $this->actingAs($leaderUser)->post('/blueteam/settings', [
            'changeNameBtn' => 1,
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSee("changeNameSubmit");
        //ChangeLeader
        $response = $this->actingAs($leaderUser)->post('/blueteam/settings', [
            'changeLeaderBtn' => 1,
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSeeInOrder(["/blueteam/changeleader", $user->username]);
        //LeaveTeam
        $response = $this->actingAs($leaderUser)->post('/blueteam/settings', [
            'leaveTeamBtn' => 1,
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSee("leaveAndStayOnTeam");
        //Not as Leader
        //LeaveTeam
        $response = $this->actingAs($user)->post('/blueteam/settings', [
            'leaveTeamBtn' => 1,
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSee("leaveAndStayOnTeam");
        $response->assertDontSee(["Change Name", "Change Leader"]);
    }

    public function testChangeName(){
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $leaderUser->setTurnTaken(1);
        $response = $this->actingAs($leaderUser)->post('/blueteam/changename', [
            'name' => "NewName",
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSee("NewName");
        $response->assertDontSee($team->name);
        $team2 = Team::factory()->create([
            'name' => "NameTaken"
        ]);
        $response = $this->actingAs($leaderUser)->post('/blueteam/changename', [
            'name' => "NameTaken",
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSee(["name-taken", "NewName"]);
        $response->assertDontSee("NameTaken");
    }

    public function testChangeLeader(){
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $team2 = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 0,
        ]);
        $leaderUser->setTurnTaken(1);
        $response = $this->actingAs($leaderUser)->post('/blueteam/changeleader', [
            'result' => $user->username,
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSeeInOrder(["Leader:", $user->username, $leaderUser->username]);
        $leaderUser->leader = 1;
        $leaderUser->update();
        $user->leader = 0;
        $user->blueteam = $team2->id;
        $user->update();
        $response = $this->actingAs($leaderUser)->post('/blueteam/changeleader', [
            'result' => $user->username,
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSee("user-not-on-team");
    }

    public function testLeaveTeam(){
        $team = Team::factory()->create();
        $leaderUser = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 0,
        ]);
        $leaderUser->setTurnTaken(1);
        $response = $this->actingAs($user)->post('/blueteam/leaveteam', [
            'result' => "invalid",
        ]);
        $response->assertViewIs('blueteam.settings');
        $response->assertSee("invalid-option");
        $response = $this->actingAs($user)->post('/blueteam/leaveteam', [
            'result' => "stay",
        ]);
        $response->assertViewIs('blueteam.settings');
        $response = $this->actingAs($user)->post('/blueteam/leaveteam', [
            'result' => "leave",
        ]);
        $response->assertViewIs('blueteam.home');
        $response->assertDontSee($team->name);
        $response = $this->actingAs($leaderUser)->post('/blueteam/leaveteam', [
            'result' => "leave",
        ]);
        $response->assertViewIs('blueteam.home');
        $response->assertDontSee($team->name);
    }

    public function testPickTarget(){
        $team = Team::factory()->create();
        $redteam = Team::factory()->red()->create();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "HeightenedAwareness",
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $user->setTurnTaken(1);
        $response = $this->actingAs($user)->post('/blueteam/picktarget', [
            'endTurn' => 1,
            'invCount' => 1,
            'result1' => $redteam->name,
            'name1' => "HeightenedAwareness",
        ]);
        $response->assertViewIs('blueteam.home');
    }

}