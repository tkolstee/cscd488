<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Http\Controllers\RedTeamController;
use App\Http\Controllers\AssetController;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use View;
use Auth;
use Exception;


class RedTeamTest extends TestCase
{
    use RefreshDatabase;

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

    public function assignTeam(){
        $user = Auth::user();
        $team = Team::factory()->red()->make();
        $team->balance = 1000;
        $team->save();
        $user->redteam = 1;
    }

    public function prefillAssets(){
        $controller = new AssetController();
        $controller->prefillTest();
    }

    public function testCreateValidRedTeam(){
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

    public function testCreateRedTeamNameAlreadyExists(){
        $team = Team::factory()->red()->make();
        $team->save();
        $controller = new RedTeamController();
        $request = Request::create('/create', 'POST', [
            'name' => $team->name,
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->create($request);
    }

    public function testDeleteValidRedTeam(){
        $team = Team::factory()->red()->make();
        $team->save();
        $controller = new RedTeamController();
        $request = Request::create('/delete', 'POST', [
            'name' => $team->name,
        ]);
        $response = $controller->delete($request);
        $this->assertTrue(Team::all()->where('name', '=', $team->name)->isEmpty());
    }

    public function testDeleteInvalidRedTeam(){
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $controller = new RedTeamController();
        $this->expectException(Exception::class);
        $response = $controller->delete($request);
    }

    public function testRedBuyValidAsset(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetRed']
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $this->assertEquals($balanceBefore-200, $response->redteam->balance);
    }

    public function testRedBuyInvalidAssetName(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['InvalidName']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testInvalidRedTeamCannotBuy(){
        $this->prefillAssets();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetRed']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testRedTeamBuyNotEnoughMoney(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetRed']
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $redteam->balance = 0;
        $redteam->update();
        $response = $controller->buy($request);
        $this->assertEquals('not-enough-money', $response->error);
    }

    public function testRedTeamBuyNoAssetSelected(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => []
        ]);
        $response = $controller->buy($request);
        $this->assertEquals('no-asset-selected', $response->error);
    }
}
