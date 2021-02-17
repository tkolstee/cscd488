<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\RedTeamController;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Asset;
use App\Models\Inventory;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;


class RedTeamTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $user = User::factory()->create();
        $this->be($user);
    }

    public function assignTeam(){
        $team = Team::factory()->red()->create([
            'balance' => 1000,
        ]);
        $user = Auth::user();
        $user->redteam = $team->id;
        $user->update();
        return $team;
    }
    
    //Create Tests
    //Should Return create view if name empty
    //Validate the name is unique
    //Return home

    public function testCreateValidRedTeam(){
        $request = Request::create('/create', 'POST', [
            'name' => 'test',
        ]);
        $controller = new RedTeamController();
        $response = $controller->create($request);
        $this->assertEquals($response->redteam->id, Auth::user()->redteam);
        $this->assertEquals('test', $response->redteam->name);
        $this->assertDatabaseHas('teams',[
            'name' => 'test'
        ]);
    }

    public function testCreateRedTeamNameAlreadyExists(){
        $team = Team::factory()->red()->make();
        $team->save();
        $controller = new RedTeamController();
        $request = Request::create('/create', 'POST', [
            'name' => $team->name,
        ]);
        $this->expectException(ValidationException::class);
        $controller->create($request);
    }

    //ChooseAttack Tests
    //Should error if no result
    //return choose attack with redteam,blueteam and possibleattacks

    public function testChooseAttackValidTeam(){
        $redteam = $this->assignTeam();
        $blueteam = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/chooseattack','POST',[
            'result' => $blueteam->name
        ]);
        $response = $controller->chooseAttack($request);
        $this->assertEquals($redteam->name, $response->redteam->name);
        $this->assertEquals($blueteam->name, $response->blueteam->name);
        $this->assertFalse(empty($response->possibleAttacks));
    }

    public function testChooseAttackNoTeam(){
        $blueteam = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/chooseattack','POST',[
            'result' => $blueteam->name
        ]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->chooseAttack($request);
    }

    public function testChooseAttackNoResults(){
        $redteam = $this->assignTeam();
        $blueteam = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/chooseattack','POST',[
            'result' => ""
        ]);
        $response = $controller->chooseAttack($request);
        $this->assertEquals("No-Team-Selected", $response->error);
    }

    //PerformAttackTests
    //Should error if no result
    //Create the attack and call onPreAttack
    //return home if attack isn'tpossible
    //return minigame view with attack, redteam, blueteam

    public function testPerformAttackNoTeam(){
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SynFlood"]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->performAttack($request);
    }

    public function testPerformAttackNoResult(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', []);
        $response = $controller->performAttack($request);
        $this->assertEquals("No-Attack-Selected", $response->error);
    }

    public function testPerformAttackPossible(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SynFlood"]);
        $response = $controller->performAttack($request);
        $this->assertEquals($team->id, $response->redteam->id);
        $this->assertEquals($team->id, $response->attack->redteam);
        $this->assertEquals($target->id, $response->attack->blueteam);
        $this->assertFalse(empty($response->attack->difficulty));
        $this->assertFalse(empty($response->attack->detection_risk));
        $this->assertTrue($response->attack->possible);
        $this->assertEquals("Syn Flood", $response->attack->name);
    }

    public function testPerformAttackNoPrereqs(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SQLInjection"]);
        $response = $controller->performAttack($request);
        $this->assertEquals("Unsatisfied prereqs for this attack", $response->attMsg);
    }

    public function testPerformAttackNoEnergy(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $team->setEnergy(0);
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SynFlood"]);
        $response = $controller->performAttack($request);
        $this->assertEquals("Not enough energy available.", $response->attMsg);
    }

    //Settings Tests
    //Should return view with redteam,changeName,and leaveTeam

    public function testSettingsNoParamValid(){
        $controller = new RedTeamController();
        $redteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[]);
        $response = $controller->settings($request);
        $this->assertEquals($redteam->id, $response->redteam->id);
        $this->assertFalse($response->changeName);
        $this->assertFalse($response->leaveTeam);
    }

    public function testSettingsNoTeamThrows(){
        $controller = new RedTeamController();
        $request = Request::create('/settings','POST',[]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->settings($request);
    }

    public function testSettingsChangeNameValid(){
        $controller = new RedTeamController();
        $redteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[
           'changeNameBtn' => 1, 
        ]);
        $response = $controller->settings($request);
        $this->assertEquals($redteam->id, $response->redteam->id);
        $this->assertTrue($response->changeName);
        $this->assertFalse($response->leaveTeam);
    }

    public function testSettingsLeaveTeamValid(){
        $controller = new RedTeamController();
        $redteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[
           'leaveTeamBtn' => 1, 
        ]);
        $response = $controller->settings($request);
        $this->assertEquals($redteam->id, $response->redteam->id);
        $this->assertFalse($response->changeName);
        $this->assertTrue($response->leaveTeam);
    }

    //ChangeName tests
    //Should throw if no team
    //Error if name taken
    //Change name if available return

    public function testChangeNameNoTeam(){
        $controller = new RedTeamController();
        $request = Request::create('/changename','POST',['name' => 'newName']);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->changeName($request);
    }

    public function testChangeNameNameTaken(){
        $this->assignTeam();
        $team2 = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/changename','POST',['name' => $team2->name]);
        $response = $controller->changeName($request);
        $this->assertEquals("name-taken", $response->error);
    }

    public function testChangeNameValid(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/changename','POST',['name' =>"new name"]);
        $response = $controller->changeName($request);
        $this->assertEquals("new name", Auth::user()->getRedTeam()->name);
    }

    //LeaveTeam tests
    //Should return to settings if stay
    //Error if not leave
    //Leaves team 

    public function testLeaveTeamNoTeam(){
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "stay"]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->leaveTeam($request);
    }

    public function testLeaveTeamBadOption(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "invalid"]);
        $response = $controller->leaveTeam($request);
        $this->assertEquals("invalid-option", $response->error);
    }

    public function testLeaveTeamStay(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "stay"]);
        $response = $controller->leaveTeam($request);
        $this->assertEquals($team->id, Auth::user()->getRedTeam()->id);
    }

    public function testLeaveTeamValid(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "leave"]);
        $response = $controller->leaveTeam($request);
        $this->assertNull(Auth::user()->redteam);
        $this->assertNull(Team::find($team->id));
    }

}
