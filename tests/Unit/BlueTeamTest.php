<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Http\Controllers\BlueTeamController;
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


class BlueTeamTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $this->login();
    }

    private function login(){
        $user = new User([
            'id' => 1,
            'name' => 'test',
        ]);
        $this->be($user);
    }

    private function assignTeam(){
        $user = Auth::user();
        $team = Team::factory()->make();
        $team->balance = 1000;
        $team->save();
        $user->blueteam = 1;
    }
    
    private function prefillAssets(){
        $controller = new AssetController();
        $controller->prefillTest();
    }

    public function testCreateValidBlueTeam(){
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

    public function testCreateBlueTeamNameAlreadyExists(){
        $team = Team::factory()->make();
        $team->save();
        $controller = new BlueTeamController();
        $request = Request::create('/create', 'POST', [
            'name' => $team->name,
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->create($request);
    }

    public function testDeleteValidBlueTeam(){
        $team = Team::factory()->make();
        $team->save();
        $controller = new BlueTeamController();
        $request = Request::create('/delete', 'POST', [
            'name' => $team->name,
        ]);
        $response = $controller->delete($request);
        $this->assertTrue(Team::all()->where('name', '=', $team->name)->isEmpty());
    }

    public function testDeleteInvalidBlueTeam(){
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $controller = new BlueTeamController();
        $this->expectException(Exception::class);
        $response = $controller->delete($request);
    }

    public function testJoinValidBlueTeam(){
        $controller = new BlueTeamController();
        $team = Team::factory()->make();
        $team->save();
        $request = Request::create('/join', 'POST', [
            'result' => $team->name,
        ]);
        $controller->join($request);
        $this->assertNotEquals(Auth::user()->blueteam, null);
    }

    public function testJoinInvalidBlueTeam(){
        $controller = new BlueTeamController();
        $request = Request::create('/join', 'POST', [
            'result' => 'invalid name',
        ]);
        $this->expectException(Exception::class);
        $response = $controller->join($request);
    }

    public function testBlueBuyValidAsset(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetBlue']
        ]);
        $blueteam = Team::find(Auth::user()->blueteam);
        $balanceBefore = $blueteam->balance;
        $response = $controller->buy($request);
        $this->assertEquals($balanceBefore-100, $response->blueteam->balance);
    }

    public function testBlueBuyInvalidAssetName(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['InvalidName']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testInvalidBlueTeamCannotBuy(){
        $this->prefillAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetBlue']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testBlueTeamBuyNotEnoughMoney(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetBlue']
        ]);
        $blueteam = Team::find(Auth::user()->blueteam);
        $blueteam->balance = 0;
        $blueteam->update();
        $response = $controller->buy($request);
        $this->assertEquals('not-enough-money', $response->error);
    }

    public function testBlueTeamBuyNoAssetSelected(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => []
        ]);
        $response = $controller->buy($request);
        $this->assertEquals('no-asset-selected', $response->error);
    }
}
