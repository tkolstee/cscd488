<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\BlueTeamController;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Inventory;
use App\Models\Asset;
use App\Models\Assets\FirewallAsset;
use App\Models\Assets\HeightenedAwarenessAsset;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;
use App\Exceptions\UserNotFoundException;


class BlueStoreTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $user = User::factory()->create();
        $this->be($user);
    }

    private function assignTeam(){
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = Auth::user();
        $user->blueteam = $team->id;
        $user->leader = 1;
        $user->update();
        return $team;
    }
    
    private function buyManyAssets(){
        $inventory = Inventory::factory()->many()->make();
        $inventory->save();
    }

    //Remove Cart Item tests
    //Should return endTurn if no cart
    //RemoveCart view if no results
    //Remove item and endturn if valid

    public function testRemoveCartItemNoCartNoResult(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->balance = 0;
        $team->update();
        $request = Request::create('/removecartitem','POST',([
            'results' => []
        ]));
        $response = $controller->removeCartItem($request);
        $this->assertEquals(0, $response->totalCost);
    }

    public function testRemoveCartItemWithCostNoResult(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->balance = 0;
        $team->update();
        $request = Request::create('/removecartitem','POST');
        $response = $controller->removeCartItem($request, 100);
        $this->assertEquals(100, $response->totalCost);
    }

    public function testRemoveCartItemNoCartWithResult(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->balance = 0;
        $team->update();
        $request = Request::create('/removecartitem','POST',([
            'results' => ['Firewall']
        ]));
        $response = $controller->removeCartItem($request);
        $this->assertEquals(1, $response->turn);
        $invs = $team->inventories();
        $this->assertNull($invs->first());
    }

    public function testRemoveCartItemWithCartInvalidResult(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $asset = new FirewallAsset;
        $team->balance = $asset->purchase_cost;
        $team->update();
        session(['buyCart' => [$asset->name, $asset->name]]);
        $request = Request::create('/removecartitem','POST',([
            'results' => ['invalid']
        ]));
        $response = $controller->removeCartItem($request);
        $buyCartAfter = session('buyCart');
        $this->assertEquals(2, count($buyCartAfter));
        $this->assertEquals($asset->purchase_cost * 2, $response->totalCost);
    }

    public function testRemoveCartItemValid(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $asset = new FirewallAsset;
        $team->balance = $asset->purchase_cost;
        $team->update();
        session(['buyCart' => [$asset->name, $asset->name]]);
        $request = Request::create('/removecartitem','POST',([
            'results' => [$asset->name]
        ]));
        $response = $controller->removeCartItem($request);
        $buyCartAfter = session('buyCart');
        $this->assertNull($buyCartAfter);
        $invs = $team->inventories();
        $this->assertEquals($asset->class_name, $invs->first()->asset_name);
        $this->assertEquals(1, $response->turn);
    }

    //Buy Tests
    //Should return error if results empty
    //Throw if asset invalid
    //Adds assets to session('buyCart') returns store

    public function testBlueBuyValidAsset(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $results = [];
        $results += ["Firewall" => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $result = $controller->buy($request);
        $buyCart = session('buyCart');
        $this->assertEquals(1, count($buyCart));
        $this->assertEquals("Firewall", $buyCart[0]);
    }

    public function testBlueBuyMultipleValid(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $results = [];
        $results += ["Firewall" => 2];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $result = $controller->buy($request);
        $buyCart = session('buyCart');
        $this->assertEquals(2, count($buyCart));
        $this->assertEquals("Firewall", $buyCart[0]);
        $this->assertEquals("Firewall", $buyCart[1]);
    }

    public function testBuyInvalidAssetName(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $results = [];
        $results += ["Invalid" => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $this->expectException(AssetNotFoundException::class);
        $controller->buy($request);
    }

    public function testInvalidBlueTeamCannotBuy(){
        $controller = new BlueTeamController();
        $results = [];
        $results += ["Firewall" => 1];
        $request = Request::create('/buy','POST', [
            'results' => $results
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->buy($request);
    }

    public function testBlueTeamBuyNoAssetSelected(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => []
        ]);
        $response = $controller->buy($request);
        $this->assertEquals('no-asset-selected', $response->error);
    }

    //Sell Tests
    //Should return error if no results
    //Throw if invalid asset
    //Add Assets to session('sellCart')

    public function testSellItemValid(){
        $blueteam = $this->assignTeam();
        $inventory = Inventory::factory()->create([
            'asset_name' => "Firewall",
            'team_id' => $blueteam->id,
            'quantity' => 1,
        ]);
        $controller = new BlueTeamController();
        $results = [];
        $results += [$inventory->id => 1];
        $request = Request::create('/sell','POST',[
            'results' => $results
        ]);
        $response = $controller->sell($request);
        $sellCart = session('sellCart');
        $this->assertEquals(1, count($sellCart));
        $this->assertEquals($inventory->id, $sellCart[0]);
    }

    public function testSellNoItem(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => []
        ]);
        $response = $controller->sell($request);
        $this->assertEquals("no-asset-selected", $response->error);
    }

    public function testSellInvalidName(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $results = [];
        $results += [-1 => 1];
        $request = Request::create('/sell','POST',[
            'results' => $results
        ]);
        $this->expectException(InventoryNotFoundException::class);
        $controller->sell($request);
    }

    public function testSellInvalidTeam(){
        $blueteam = Team::factory()->create();
        $inventory = Inventory::factory()->create([
            'asset_name' => "Firewall",
            'team_id' => $blueteam->id,
            'quantity' => 1,
        ]);
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$inventory->id]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->sell($request);
    }

}