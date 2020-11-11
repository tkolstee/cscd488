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
use App\Models\Asset;
use App\Models\Inventory;
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
        $asset = Asset::factory()->red()->make();
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
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $this->assertEquals($balanceBefore-200, $response->redteam->balance);
    }

    public function testBuyAlreadyOwned(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $this->buyAssets();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $quantBefore = Inventory::all()->where('team_id','=',Auth::user()->redteam)->first()->quantity;
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-200, $response->redteam->balance);
        $this->assertEquals(1, $inventory->team_id);
        $this->assertEquals(substr(Asset::where('name','=',$assetName)->pluck('id'),1,1), $inventory->asset_id);
        $this->assertEquals($quantBefore + 1, $inventory->quantity);
    }

    public function testBuyInvalidAssetName(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['InvalidName']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testInvalidRedTeamCannotBuy(){
        $assetName = $this->prefillAssets();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $this->expectException(Exception::class);
        $response = $controller->buy($request);
    }

    public function testRedTeamBuyNotEnoughMoney(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $redteam->balance = 0;
        $redteam->update();
        $response = $controller->buy($request);
        $this->assertEquals('not-enough-money', $response->error);
    }

    public function testRedTeamBuyNoAssetSelected(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
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
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $balBefore = Team::find(Auth::user()->redteam)->balance;
        $assetPrice = Asset::find(1)->purchase_cost;
        $response = $controller->sell($request);
        $inventory = Inventory::all()->where('team_id','=',Auth::user()->redteam);
        $this->assertEquals($balBefore+$assetPrice, $response->redteam->balance);
        $this->assertTrue($inventory->isEmpty());
    }
    public function testSellItemOwnedManyValid(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $this->buyManyAssets();
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $balBefore = Team::find(Auth::user()->redteam)->balance;
        $assetPrice = Asset::find(1)->purchase_cost;
        $quantBefore = Inventory::all()->where('team_id','=',Auth::user()->redteam)->first()->quantity;
        $response = $controller->sell($request);
        $inventory = Inventory::all()->where('team_id','=',Auth::user()->redteam)->first();
        $this->assertEquals($balBefore+$assetPrice, $response->redteam->balance);
        $this->assertEquals($quantBefore - 1, $inventory->quantity);
    }

    public function testSellItemNotOwned(){
        $assetName = $this->prefillAssets();
        $this->assignTeam();
        $controller = new RedTeamController();
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
        $controller = new RedTeamController();
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
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => ['invalidName']
        ]);
        $this->expectException(Exception::class);
        $response = $controller->sell($request);
    }

    public function testSellInvalidTeam(){
        $assetName = $this->prefillAssets();
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $this->expectException(Exception::class);
        $response = $controller->sell($request);
    }
}
