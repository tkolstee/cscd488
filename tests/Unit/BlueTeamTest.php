<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Http\Controllers\BlueTeamController;
use App\Http\Controllers\AssetController;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Inventory;
use App\Models\Asset;
use View;
use Auth;
use Exception;


class BlueTeamTest extends TestCase
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

    public function assignTeam(){
        $user = Auth::user();
        $team = Team::factory()->make();
        $team->balance = 1000;
        $team->save();
        $user->blueteam = 1;
    }
    
    public function prefillAssets(){
        $controller = new AssetController();
        $controller->prefillTest();
    }

    public function testCreateValid(){
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
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $controller = new BlueTeamController();
        $this->expectException(Exception::class);
        $response = $controller->delete($request);
    }

    public function testJoinValid(){
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
        $controller = new BlueTeamController();
        $request = Request::create('/join', 'POST', [
            'result' => 'invalid name',
        ]);
        $this->expectException(Exception::class);
        $response = $controller->join($request);
    }

    public function testBuyValid(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetBlue']
        ]);
        $blueteam = Team::find(Auth::user()->blueteam);
        $balanceBefore = $blueteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-100, $response->blueteam->balance);
        $this->assertEquals(1, $inventory->team_id);
        $this->assertEquals(substr(Asset::where('name','=','TestAssetBlue')->pluck('id'),1,1), $inventory->asset_id);
    }

    public function testBuyInvalidAssetName(){
        $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['InvalidName']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testBuyInvalidTeam(){
        $this->prefillAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['TestAssetBlue']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testBuyNotEnoughMoney(){
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

    public function testBuyNoAssetSelected(){
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
