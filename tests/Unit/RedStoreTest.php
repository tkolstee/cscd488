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


class RedStoreTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $user = User::factory()->create();
        $this->be($user);
    }

    public function assignTeam(){
        $team = Team::factory()->red()->create([
            'balance' => 5000,
        ]);
        $user = Auth::user();
        $user->redteam = $team->id;
        $user->update();
        return $team;
    }
 
    //Buy Tests
    //Should error on no results, not enough money
    //Buy all items if you have enough

    public function testRedBuyValidAsset(){
        $asset = Asset::getBuyableRed()[0];
        $this->assignTeam();
        $controller = new RedTeamController();
        $results = [];
        $results += [$asset->class_name => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-($asset->purchase_cost), $response->redteam->balance);
        $this->assertEquals(1, $inventory->quantity);
    }

    public function testRedBuyMultipleValid(){
        $asset = Asset::getBuyableRed()[0];
        $this->assignTeam();
        $controller = new RedTeamController();
        $results = [];
        $results += [$asset->class_name => 2];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-($asset->purchase_cost * 2), $response->redteam->balance);
        $this->assertEquals(2, $inventory->quantity);
    }

    public function testBuyAlreadyOwned(){
        $redteam = $this->assignTeam();
        $asset = Asset::getBuyableRed()[0];
        $inventory = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $redteam->id,
            'quantity' => 1
        ]);
        $controller = new RedTeamController();
        $results = [];
        $results += [$asset->class_name => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $quantBefore = Inventory::all()->where('team_id','=',$redteam->id)->where('asset_name','=',$asset->class_name)->first()->quantity;
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balanceBefore - $asset->purchase_cost, $response->redteam->balance);
        $this->assertEquals($quantBefore + 1, $inventory->quantity);
    }

    public function testBuyInvalidAssetName(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $results = [];
        $results += ["InvalidName" => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $this->expectException(AssetNotFoundException::class);
        $controller->buy($request);
    }

    public function testBuyNoTeam(){
        $asset = Asset::getBuyableRed()[0];
        $controller = new RedTeamController();
        $results = [];
        $results += [$asset->class_name => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->buy($request);
    }

    public function testBuyBuyNotEnoughMoney(){
        $asset = Asset::getBuyableRed()[0];
        $redteam = $this->assignTeam();
        $controller = new RedTeamController();
        $results = [];
        $results += [$asset->class_name => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $redteam->balance = 0;
        $redteam->update();
        $response = $controller->buy($request);
        $this->assertEquals('not-enough-money', $response->error);
    }

    public function testBuyBuyMultipleNotEnoughMoney(){
        $asset = Asset::getBuyableRed()[0];
        $redteam = $this->assignTeam();
        $controller = new RedTeamController();
        $results = [];
        $results += [$asset->class_name => 2];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $redteam->balance = $asset->purchase_cost;
        $redteam->update();
        $response = $controller->buy($request);
        $this->assertEquals('not-enough-money', $response->error);
        $invs = $redteam->inventories();
        $this->assertEmpty($invs);
    }

    public function testRedTeamBuyNoAssetSelected(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => []
        ]);
        $response = $controller->buy($request);
        $this->assertEquals('no-asset-selected', $response->error);
    }

    //Sell Tests
    //Should error if no results, or don't own asset
    //Sell all items

    public function testSellItemOwnedOneValid(){
        $asset = Asset::getBuyableRed()[0];
        $redteam = $this->assignTeam();
        $inventory = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $redteam->id,
            'quantity' => 1,
        ]);
        $controller = new RedTeamController();
        $results = [];
        $results += [$inventory->id => 1];
        $request = Request::create('/sell','POST',[
            'results' => $results
        ]);
        $balBefore = $redteam->balance;
        $response = $controller->sell($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balBefore+$asset->purchase_cost, $response->redteam->balance);
        $this->assertTrue($inventory == null);
    }

    public function testSellItemOwnedManyValid(){
        $asset = Asset::getBuyableRed()[0];
        $redteam = $this->assignTeam();
        $inventory = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $redteam->id,
            'quantity' => 5,
        ]);
        $controller = new RedTeamController();
        $results = [];
        $results += [$inventory->id => 1];
        $request = Request::create('/sell','POST',[
            'results' => $results
        ]);
        $balBefore = $redteam->balance;
        $quantBefore = $inventory->quantity;
        $response = $controller->sell($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balBefore+$asset->purchase_cost, $response->redteam->balance);
        $this->assertEquals($quantBefore - 1, $inventory->quantity);
    }

    public function testRedSellMultipleItems(){
        $redteam = $this->assignTeam();
        $asset1 = Asset::getBuyableRed()[0];
        $inventory1 = Inventory::factory()->create([
            'asset_name' => $asset1->class_name,
            'team_id' => $redteam->id,
            'quantity' => 3,
        ]);
        $controller = new RedTeamController();
        $results = [];
        $results += [$inventory1->id => 2];
        $request = Request::create('/sell','POST',[
            'results' => $results
        ]);
        $balanceBefore = $redteam->balance;
        $qtyBefore1 = $inventory1->quantity;
        $controller->sell($request);
        $inventory1 = Inventory::find($inventory1->id);
        $redteam = Team::find($redteam->id);
        $this->assertEquals($qtyBefore1-2, $inventory1->quantity);
        $expectedBalance = $balanceBefore + $asset1->purchase_cost + $asset1->purchase_cost;
        $this->assertEquals($expectedBalance, $redteam->balance);
    }

    public function testSellItemNotOwned(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $team = Team::factory()->red()->create();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "Firewall"
        ]);
        $results = [];
        $results += [$inv->id => 1];
        $request = Request::create('/sell','POST',[
            'results' => $results
        ]);
        $response = $controller->sell($request);
        $this->assertEquals("not-enough-owned", $response->error);
    }

    public function testSellNoItem(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => []
        ]);
        $response = $controller->sell($request);
        $this->assertEquals("no-asset-selected", $response->error);
    }

    public function testSellInvalidName(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $results = [];
        $results += [1 => 1];
        $request = Request::create('/sell','POST',[
            'results' => $results
        ]);
        $response = $controller->sell($request);
        $this->assertEquals("not-enough-owned", $response->error);
    }

    public function testSellInvalidTeam(){
        $asset = Asset::getBuyableRed()[0];
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->class_name]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->sell($request);
    }

}