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
        $request = Request::create('/create', 'POST', [
            'name' => 'test',
        ]);
        $controller = new BlueTeamController();
        $response = $controller->create($request);
        $this->expectException(ValidationException::class);
        $response = $controller->create($request);
    }

    public function testDeleteValid(){
        $this->login();
        $request = Request::create('/create', 'POST', [
            'name' => 'test',
        ]);
        $controller = new BlueTeamController();
        $controller->create($request);
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $response = $controller->delete($request);
        $this->assertTrue(Team::all()->where('name', '=', 'test')->isEmpty());
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

}
