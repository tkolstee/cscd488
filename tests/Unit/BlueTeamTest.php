<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Http\Controllers\BlueTeamController;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use View;
use Auth;
use Exception;


class BlueTeamTest extends TestCase
{
    use DatabaseMigrations;

    /*public function setUp(){
        parent::setUp();
        $this->login();
    }*/

    public function login(){
        $user = new User([
            'id' => 1,
            'name' => 'test',
        ]);
        $this->be($user);
    }

    public function testCreateValid(){
        $this->login();
        $request = Request::create('/create', 'POST', [
            'name' => 'test',
        ]);
        $controller = new BlueTeamController();
        $response = $controller->create($request);
        $this->assertEquals('test', $response->blueteam->name);
        $this->assertDatabaseHas('teams',[
            'name' => 'test'
        ]);
    }

    public function testCreateNameAlreadyExists(){
        $this->login();
        $team = Team::factory()->make();
        $team->save();
        $controller = new BlueTeamController();
        $request = Request::create('/create', 'POST', [
            'name' => $team->name,
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->create($request);
    }

    public function testDeleteValid(){
        $this->login();
        $team = Team::factory()->make();
        $team->save();
        $controller = new BlueTeamController();
        $request = Request::create('/delete', 'POST', [
            'name' => $team->name,
        ]);
        $response = $controller->delete($request);
        $this->assertTrue(Team::all()->where('name', '=', $team->name)->isEmpty());
    }

    public function testDeleteInvalid(){
        $this->login();
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $controller = new BlueTeamController();
        $this->expectException(Exception::class);
        $response = $controller->delete($request);
    }

    public function testJoinValid(){
        $this->login();
        $controller = new BlueTeamController();
        $team = Team::factory()->make();
        $team->save();
        $request = Request::create('/join', 'POST', [
            'result' => $team->name,
        ]);
        $controller->join($request);
        $this->assertNotEquals(Auth::user()->blueteam, null);
    }

    public function testJoinInvalid(){
        $this->login();
        $controller = new BlueTeamController();
        $request = Request::create('/join', 'POST', [
            'result' => 'invalid name',
        ]);
        $this->expectException(Exception::class);
        $response = $controller->join($request);
    }

}
