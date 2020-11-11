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
        $asset = Asset::factory()->make();
        $asset->save();
        return $asset->name;
    }
    
    private function buyAssets(){
        $inventory = Inventory::factory()->make();
        $inventory->save();
    }
    private function buyManyAssets(){
        $inventory = Inventory::factory()->many()->make();
        $inventory->save();
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
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $blueteam = Team::find(Auth::user()->blueteam);
        $balanceBefore = $blueteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-100, $response->blueteam->balance);
        $this->assertEquals(1, $inventory->team_id);
        $this->assertEquals(substr(Asset::where('name','=',$assetName)->pluck('id'),1,1), $inventory->asset_id);
    }

    public function testBuyAlreadyOwned(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $this->buyAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $blueteam = Team::find(Auth::user()->blueteam);
        $quantBefore = Inventory::all()->where('team_id','=',Auth::user()->blueteam)->first()->quantity;
        $balanceBefore = $blueteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-100, $response->blueteam->balance);
        $this->assertEquals(1, $inventory->team_id);
        $this->assertEquals(substr(Asset::where('name','=',$assetName)->pluck('id'),1,1), $inventory->asset_id);
        $this->assertEquals($quantBefore + 1, $inventory->quantity);
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
        $assetName = $this->prefillAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testBuyNotEnoughMoney(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
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

    public function testSellItemOwnedOneValid(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $this->buyAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $balBefore = Team::find(Auth::user()->blueteam)->balance;
        $assetPrice = Asset::find(1)->purchase_cost;
        $response = $controller->sell($request);
        $inventory = Inventory::all()->where('team_id','=',Auth::user()->blueteam);
        $this->assertEquals($balBefore+$assetPrice, $response->blueteam->balance);
        $this->assertTrue($inventory->isEmpty());
    }
    public function testSellItemOwnedManyValid(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $this->buyManyAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $balBefore = Team::find(Auth::user()->blueteam)->balance;
        $assetPrice = Asset::find(1)->purchase_cost;
        $quantBefore = Inventory::all()->where('team_id','=',Auth::user()->blueteam)->first()->quantity;
        $response = $controller->sell($request);
        $inventory = Inventory::all()->where('team_id','=',Auth::user()->blueteam)->first();
        $this->assertEquals($balBefore+$assetPrice, $response->blueteam->balance);
        $this->assertEquals($quantBefore - 1, $inventory->quantity);
    }

    public function testSellItemNotOwned(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $this->expectException(Exception::class);
        $response = $controller->sell($request);
    }

    public function testSellNoItem(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $this->buyAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => []
        ]);
        $response = $controller->sell($request);
        $this->assertEquals("no-asset-selected", $response->error);
    }

    public function testSellInvalidName(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $this->buyAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => ['invalidName']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->sell($request);
    }

    public function testSellInvalidTeam(){
        $assetName = $this->prefillAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $this->expectException(Exception::class);
        $response = $controller->sell($request);
    }

}
