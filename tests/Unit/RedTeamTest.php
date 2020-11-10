<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Http\Controllers\RedTeamController;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use View;
use Auth;
use Exception;


class RedTeamTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void{
        parent::setUp();
        $this->login();
    }

    public function login(){
        $user = new User([
            'id' => 1,
            'name' => 'test',
        ]);
        $this->be($user);
    }

    public function testCreateValid(){
        $request = Request::create('/create', 'POST', [
            'name' => 'test',
        ]);
        $controller = new RedTeamController();
        $response = $controller->create($request);
        $this->assertEquals('test', $response->redteam->name);
        $this->assertDatabaseHas('teams',[
            'name' => 'test'
        ]);
    }

    public function testCreateNameAlreadyExists(){
        $team = Team::factory()->red()->make();
        $team->save();
        $controller = new RedTeamController();
        $request = Request::create('/create', 'POST', [
            'name' => $team->name,
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->create($request);
    }

    public function testDeleteValid(){
        $team = Team::factory()->red()->make();
        $team->save();
        $controller = new RedTeamController();
        $request = Request::create('/delete', 'POST', [
            'name' => $team->name,
        ]);
        $response = $controller->delete($request);
        $this->assertTrue(Team::all()->where('name', '=', $team->name)->isEmpty());
    }

    public function testDeleteInvalid(){
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $controller = new RedTeamController();
        $this->expectException(Exception::class);
        $response = $controller->delete($request);
    }

}
