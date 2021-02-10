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


class BlueTeamTest extends TestCase
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

    //Home tests
    //Should Return home with no team 
    //or with Team and leader ( + members )
    //with Turn number

    public function testHomeNoTeam(){
        $controller = new BlueteamController();
        $response = $controller->home();
        $this->assertTrue(empty($response->turn));
        $this->assertTrue(empty($response->blueteam));
        $this->assertTrue(empty($response->leader));
        $this->assertTrue(empty($response->members));
    }

    public function testHomeWithTeamNoMembers(){
       $this->assignTeam();
       $controller = new BlueteamController();
       $response = $controller->home();
       $teamID = Auth::user()->blueteam;
       $teamName = Team::find($teamID);
       $this->assertEquals($teamName, $response->blueteam);
       $this->assertEquals(Auth::user()->id, $response->leader->id);
       $this->assertTrue($response->members->isEmpty());
       $this->assertEquals(0 , $response->turn);
    }

    public function testHomeWithTeamAndMembers(){
        $this->assignTeam();
        $teamID = Auth::user()->blueteam;
        $user1 = User::factory()->create(['blueteam' => $teamID, ]);
        $user2 = User::factory()->create(['blueteam' => $teamID, ]);
        $controller = new BlueteamController();
        $response = $controller->home();
        $teamName = Team::find($teamID);
        $this->assertEquals($teamName, $response->blueteam);
        $this->assertEquals(Auth::user()->id, $response->leader->id);
        $this->assertEquals(2, count($response->members));
        $this->assertNotNull($response->members->find($user1->id));
        $this->assertNotNull($response->members->find($user2->id));      
        $this->assertEquals(0, $response->turn);
    }

    //Create Tests
    //Should Return create view if name empty
    //Validate the name is unique
    //Return home

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
        $this->assertEquals(1, Auth::user()->leader);
    }

    public function testCreateBlueTeamNameAlreadyExists(){
        $team = Team::factory()->make();
        $team->save();
        $controller = new BlueTeamController();
        $request = Request::create('/create', 'POST', [
            'name' => $team->name,
        ]);
        $this->expectException(ValidationException::class);
        $controller->create($request);
    }

    //Join Tests
    //Should return join view with all blueteams if result empty
    //Call User::joinBlueTeam with teamName and return home

    public function testJoinValidBlueTeam(){
        $controller = new BlueTeamController();
        $team = Team::factory()->create();
        $request = Request::create('/join', 'POST', [
            'result' => $team->name,
        ]);
        $controller->join($request);
        $this->assertEquals($team->id ,Auth::user()->blueteam);
        $this->assertEquals(0, Auth::user()->leader);
    }

    public function testJoinInvalidBlueTeam(){
        $controller = new BlueTeamController();
        $request = Request::create('/join', 'POST', [
            'result' => 'invalid name',
        ]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->join($request);
    }

    //Buy Tests
    //Should return error if results empty
    //Throw if asset invalid
    //Adds assets to session('buyCart') returns store

    public function testBlueBuyValidAsset(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ["Firewall"]
        ]);
        $result = $controller->buy($request);
        $buyCart = session('buyCart');
        $this->assertEquals(1, count($buyCart));
        $this->assertEquals("Firewall", $buyCart[0]);
    }

    public function testBuyInvalidAssetName(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ["Invalid"]
        ]);
        $this->expectException(AssetNotFoundException::class);
        $controller->buy($request);
    }

    public function testInvalidBlueTeamCannotBuy(){
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ["Firewall"]
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
        $request = Request::create('/sell','POST',[
            'results' => [$inventory->id]
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
        $request = Request::create('/sell','POST',[
            'results' => ["Invalid"]
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

    //EndTurn Tests
    //Should return error if don't own or don't have enough money, and remove from cart after processed
    //throw if no team, invalid asset, 
    //Buy all in buycart, sell in sellcart, set sessions null, set turntaken to 1, return home with endtime

    public function testEndTurnNoTeamThrows(){
        $controller = new BlueTeamController();
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->endTurn();
    }

    public function testEndTurnInvalidAssetInSellCart(){
        $controller = new BlueTeamController();
        $this->assignTeam();
        $sellCart[] = 'invalid';
        session(['sellCart' => $sellCart]);
        $response = $controller->endTurn();
        $this->assertEquals('not-enough-owned', $response->error);
        $newSellCart = session('sellCart');
        $this->assertEquals(0, count($newSellCart));
    }

    public function testEndTurnInvalidAssetInBuyCart(){
        $controller = new BlueTeamController();
        $this->assignTeam();
        $buyCart[] = 'invalid';
        session(['buyCart' => $buyCart]);
        $this->expectException(AssetNotFoundException::class);
        $response = $controller->endTurn();
    }

    public function testEndTurnNoItemOwnedError(){
        $controller = new BlueTeamController();
        $this->assignTeam();
        $sellCart[] = "1";
        session(['sellCart' => $sellCart]);
        $response = $controller->endTurn();
        $this->assertEquals('not-enough-owned', $response->error);
        $newSellCart = session('sellCart');
        $this->assertEquals(0, count($newSellCart));
    }

    public function testEndTurnNotEnoughMoney(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->balance = 0;
        $team->update();
        $asset = new FirewallAsset;
        $buyCart[] = $asset->name;
        session(['buyCart' => $buyCart]);
        $response = $controller->endTurn();
        $newBuyCart = session('buyCart');
        $this->assertNull($newBuyCart);
        $this->assertEquals('no-money', $response->error);
    }

    public function testEndTurnBuyOne(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $balBefore = $team->balance;
        $inventoryBefore = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"Firewall")->first();
        if($inventoryBefore == null) $quantity = 0;
        else $quantity = $inventoryBefore->quantity;
        $buyCart[] = "Firewall";
        session(['buyCart' => $buyCart]);
        $response = $controller->endTurn();
        $newBuyCart = session('buyCart');
        $this->assertNull($newBuyCart);
        $inventoryAfter = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"Firewall")->first();
        $this->assertEquals($quantity + 1, $inventoryAfter->quantity);
        $balAfter = Auth::user()->getBlueTeam()->balance;
        $this->assertEquals($balBefore - Asset::get("Firewall")->purchase_cost, $balAfter);
        $this->assertEquals(1, $response->turn);
        //$this->assertFalse(empty($response->endTime));
    }

    public function testEndTurnBuyMany(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->update();
        $balBefore = $team->balance;
        
        $inventoryBefore = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"Firewall")->first();
        if($inventoryBefore == null) $quantity = 0;
        else $quantity = $inventoryBefore->quantity;
        $buyCart[] = "Firewall";
        $buyCart[] = "Firewall";
        session(['buyCart' => $buyCart]);
        $response = $controller->endTurn();
        $newBuyCart = session('buyCart');
        $this->assertNull($newBuyCart);
        $inventoryAfter = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"Firewall")->first();
        $this->assertEquals($quantity + 2, $inventoryAfter->quantity);
        $balAfter = Auth::user()->getBlueTeam()->balance;
        $this->assertEquals($balBefore - (Asset::get("Firewall")->purchase_cost * 2), $balAfter);
        $this->assertEquals(1, $response->turn);
        //$this->assertFalse(empty($response->endTime));
    }

    public function testEndTurnSellOne(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $balBefore = $team->balance;
        $inventory = Inventory::factory()->create(['asset_name' => "Firewall", 'team_id' => $team->id, 'quantity' => 1]);
        $sellCart[] = $inventory->id;
        session(['sellCart' => $sellCart]);
        $response = $controller->endTurn();
        $newSellCart = session('sellCart');
        $this->assertNull($newSellCart);
        $inventoryAfter = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"Firewall")->first();
        $this->assertNull($inventoryAfter);
        $balAfter = Auth::user()->getBlueTeam()->balance;
        $this->assertEquals($balBefore + Asset::get("Firewall")->purchase_cost, $balAfter);
        $this->assertEquals(1, $response->turn);
        //$this->assertFalse(empty($response->endTime));
    }

    public function testEndTurnSellMany(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->update();
        $balBefore = $team->balance;
        $inventory = Inventory::factory()->create(['asset_name' => "Firewall", 'team_id' => $team->id, 'quantity' => 2]);
        $sellCart[] = $inventory->id;
        $sellCart[] = $inventory->id;
        session(['sellCart' => $sellCart]);
        $response = $controller->endTurn();
        $newSellCart = session('sellCart');
        $this->assertNull($newSellCart);
        $inventoryAfter = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"Firewall")->first();
        $this->assertNull($inventoryAfter);
        $balAfter = Auth::user()->getBlueTeam()->balance;
        $this->assertEquals($balBefore + 2 * Asset::get("Firewall")->purchase_cost, $balAfter);
        $this->assertEquals(1, $response->turn);
        //$this->assertFalse(empty($response->endTime));
    }

    public function testEndTurnBuyAndSellMany(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->update();
        $balBefore = $team->balance;
        $inventory = Inventory::factory()->create(['asset_name' => "Firewall", 'team_id' => $team->id, 'quantity' => 2]);
        $sellCart[] = $inventory->id;
        $sellCart[] = $inventory->id;
        $buyCart[] = "SQL Database";
        $buyCart[] = "SQL Database";
        session(['sellCart' => $sellCart]);
        session(['buyCart' => $buyCart]);
        $response = $controller->endTurn();
        $newSellCart = session('sellCart');
        $newBuyCart = session('buyCart');
        $this->assertNull($newBuyCart);
        $this->assertNull($newSellCart);
        $inventoryAfterBuy = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"SQLDatabase")->first();
        $inventoryAfterSell = Inventory::all()->where('team_id','=',$team->id)->where('asset_name','=',"Firewall")->first();
        $this->assertNull($inventoryAfterSell);
        $this->assertEquals(2, $inventoryAfterBuy->quantity);
        $balAfter = Auth::user()->getBlueTeam()->balance;
        $this->assertEquals($balBefore + 2 * Asset::get("Firewall")->purchase_cost - 2 * Asset::get("SQLDatabase")->purchase_cost, $balAfter);
        $this->assertEquals(1, $response->turn);
        //$this->assertFalse(empty($response->endTime));
    }

    //Settings Tests
    //Should return view with blueteam,leader,members,changeName,and leaveTeam

    public function testSettingsNoParamValid(){
        $controller = new BlueTeamController();
        $blueteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[]);
        $response = $controller->settings($request);
        $this->assertEquals($blueteam->id, $response->blueteam->id);
        $this->assertEquals(Auth::user()->id, $response->leader->id);
        $this->assertEquals(0 , count($response->members));
        $this->assertFalse($response->changeName);
        $this->assertFalse($response->leaveTeam);
    }

    public function testSettingsNoTeamThrows(){
        $controller = new BlueTeamController();
        $request = Request::create('/settings','POST',[]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->settings($request);
    }

    public function testSettingsChangeNameValid(){
        $controller = new BlueTeamController();
        $blueteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[
           'changeNameBtn' => 1, 
        ]);
        $response = $controller->settings($request);
        $this->assertEquals($blueteam->id, $response->blueteam->id);
        $this->assertEquals(Auth::user()->id, $response->leader->id);
        $this->assertEquals(0 , count($response->members));
        $this->assertTrue($response->changeName);
        $this->assertFalse($response->leaveTeam);
    }

    public function testSettingsLeaveTeamValid(){
        $controller = new BlueTeamController();
        $blueteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[
           'leaveTeamBtn' => 1, 
        ]);
        $response = $controller->settings($request);
        $this->assertEquals($blueteam->id, $response->blueteam->id);
        $this->assertEquals(Auth::user()->id, $response->leader->id);
        $this->assertEquals(0 , count($response->members));
        $this->assertFalse($response->changeName);
        $this->assertTrue($response->leaveTeam);
    }

    public function testSettingsHasMembersValid(){
        $controller = new BlueTeamController();
        $blueteam = $this->assignTeam();
        $member1 = User::factory()->create([
            'blueteam' => $blueteam->id,
        ]);
        $member2 = User::factory()->create([
            'blueteam' => $blueteam->id,
        ]);
        $request = Request::create('/settings','POST',[]);
        $response = $controller->settings($request);
        $this->assertEquals($blueteam->id, $response->blueteam->id);
        $this->assertEquals(Auth::user()->id, $response->leader->id);
        $this->assertEquals(2 , count($response->members));
        $this->assertFalse($response->changeName);
        $this->assertFalse($response->leaveTeam);
    }

    //ChangeName tests
    //Should throw if no team
    //Error if name taken
    //Change name if available return

    public function testChangeNameNoTeam(){
        $controller = new BlueTeamController();
        $request = Request::create('/changename','POST',['name' => 'newName']);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->changeName($request);
    }

    public function testChangeNameNameTaken(){
        $this->assignTeam();
        $team2 = Team::factory()->create();
        $controller = new BlueTeamController();
        $request = Request::create('/changename','POST',['name' => $team2->name]);
        $response = $controller->changeName($request);
        $this->assertEquals("name-taken", $response->error);
    }

    public function testChangeNameValid(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/changename','POST',['name' =>"new name"]);
        $response = $controller->changeName($request);
        $this->assertEquals("new name", Auth::user()->getBlueTeam()->name);
    }

    //LeaveTeam tests
    //Should return to settings if stay
    //Error if not leave
    //Leaves team 

    public function testLeaveTeamNoTeam(){
        $controller = new BlueTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "stay"]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->leaveTeam($request);
    }

    public function testLeaveTeamBadOption(){
        $team = $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "invalid"]);
        $response = $controller->leaveTeam($request);
        $this->assertEquals("invalid-option", $response->error);
    }

    public function testLeaveTeamStay(){
        $team = $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "stay"]);
        $response = $controller->leaveTeam($request);
        $this->assertEquals($team->id, Auth::user()->getBlueTeam()->id);
    }

    public function testLeaveTeamLeaderNoMembers(){
        $team = $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "leave"]);
        $response = $controller->leaveTeam($request);
        $this->assertNull(Auth::user()->blueteam);
        $this->assertNull(Team::find($team->id));
    }

    public function testLeaveTeamLeaderWithMembers(){
        $team = $this->assignTeam();
        $user1 = User::factory()->create(['blueteam' => $team->id]);
        $controller = new BlueTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "leave"]);
        $response = $controller->leaveTeam($request);
        $this->assertNull(Auth::user()->blueteam);
        $this->assertNotNull(Team::find($team->id));
    }

    //Upgrade Tests
    //Should return inventory with correct upgrade
    //error if costs too much or level max
    //exception if not owned

    public function testUpgradeNoTeam(){
        $controller = new BlueTeamController();
        $request = Request::create('/upgrade', 'POST', []);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->leaveTeam($request);
    }  

    public function testUpgradeNotOwned(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $request = Request::create('/upgrade', 'POST', ['submit' => "SQLDatabase1"]);
        $this->expectException(InventoryNotFoundException::class);
        $response = $controller->upgrade($request);
    }

    public function testUpgradeNotEnoughMoney(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team->balance = 0;
        $team->update();
        $inventory = Inventory::factory()->create(['asset_name' => "Firewall", 'team_id' => $team->id, 'quantity' => 1]);
        $request = Request::create('/upgrade', 'POST', ['submit' => $inventory->id]);
        $response = $controller->upgrade($request);
        $this->assertEquals("unsuccessful", $response->error);
    }

    public function testUpgradeAlreadyMax(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $inventory = Inventory::factory()->create(['asset_name' => "Firewall", 'team_id' => $team->id, 'quantity' => 1, 'level' => 3]);
        $request = Request::create('/upgrade', 'POST', ['submit' => $inventory->id]);
        $response = $controller->upgrade($request);
        $this->assertEquals("unsuccessful", $response->error);
    }

    public function testUpgradeValid(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $inventory2 = Inventory::factory()->create(['asset_name' => "Firewall", 'team_id' => $team->id, 'quantity' => 2, 'level' => 2]);
        $inventory1 = Inventory::factory()->create(['asset_name' => "Firewall", 'team_id' => $team->id, 'quantity' => 2, 'level' => 1]);
        $request = Request::create('/upgrade', 'POST', ['submit' => $inventory1->id]);
        $response = $controller->upgrade($request);
        $inventory1After = $team->inventory(Asset::get($inventory1->asset_name), $inventory1->level);        
        $inventory2After = $team->inventory(Asset::get($inventory2->asset_name), $inventory2->level);
        $this->assertEquals($inventory1->quantity - 1, $inventory1After->quantity);
        $this->assertEquals($inventory2->quantity + 1, $inventory2After->quantity);
    }

    //ChangeLeader tests
    //Should return settings with leader changed or error if user not on correct team
    //throws UserNotFoundException if incorrect username

    public function testChangeLeaderInvalidName(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $request = Request::create('/changeLeader', 'POST', ['result' => 'invalidName']);
        $this->expectException(UserNotFoundException::class);
        $controller->changeLeader($request);
    }

    public function testChangeLeaderNotAMember(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $team2 = Team::factory()->create();
        $user1 = User::factory()->create(['blueteam' => $team2->id]);
        $request = Request::create('/changeLeader', 'POST', ['result' => $user1->username]);
        $response = $controller->changeLeader($request);
        $this->assertEquals("user-not-on-team", $response->error);
    }

    public function testChangeLeaderValid(){
        $controller = new BlueTeamController();
        $team = $this->assignTeam();
        $user1 = User::factory()->create(['blueteam' => $team->id]);
        $request = Request::create('/changeLeader', 'POST', ['result' => $user1->username]);
        $response = $controller->changeLeader($request);
        $user1After = User::find($user1->id);
        $this->assertEquals(0, Auth::user()->leader);
        $this->assertEquals(1, $user1After->leader);
        $this->assertEquals($user1->username, $team->leader()->username);
    }

    //JoinMembers Test
    //Should return join page with team name and members + leader
    //Throws TeamNotFound if invalid team name

    public function testJoinMembersInvalidTeam(){
        $controller = new BlueTeamController();
        $request = Request::create('/joinmembers', 'POST', ['submit' => 'InvalidName']);
        $this->expectException(TeamNotFoundException::class);
        $controller->joinMembers($request);
    }

    public function testJoinMembersValid(){
        $controller = new BlueTeamController();
        $team = Team::factory()->create();
        $user1 = User::factory()->create(['blueteam' => $team->id, 'leader' => 1]);
        $user2 = User::factory()->create(['blueteam' => $team->id, 'leader' => 0]);
        $request = Request::create('/joinmembers', 'POST', ['submit' => $team->name]);
        $response = $controller->joinMembers($request);
        $this->assertEquals($team->name, $response->viewMembers);
        $this->assertEquals($user1->id, $response->viewTeamLeader->id);
        $this->assertEquals($user2->id, $response->viewTeamMembers->first()->id);
    }

    //PickTarget Test
    //Should return home with turn ended
    
    public function testPickTargetInvalidTeam(){
        $controller = new BlueTeamController();
        $team = Team::factory()->create();
        $user1 = User::factory()->create(['blueteam' => $team->id, 'leader' => 1]);
        $asset = new HeightenedAwarenessAsset();
        $inv = Inventory::factory()->create(['asset_name' => $asset->class_name, 'team_id' => $team->id]);
        $invs = [$inv];
        $request = Request::create('/picktarget','POST',['invs' => $invs, 'result1' => "InvalidName"]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->pickTarget($request);
    }
    
    public function testPickTarget1AssetValid(){
        $controller = new BlueTeamController();
        $team = Team::factory()->create();
        $redteam = Team::factory()->red()->create();
        $user1 = User::factory()->create(['blueteam' => $team->id, 'leader' => 1]);
        $this->be($user1);
        $user2 = User::factory()->create(['redteam' => $redteam->id]);
        $asset = Asset::get("HeightenedAwareness");
        $inv = Inventory::factory()->create(['asset_name' => $asset->class_name, 'team_id' => $team->id]);
        $invs = [$inv];
        $request = Request::create('/picktarget','POST',['invCount' => 1, 'result1' => $redteam->name, 'name1' => $asset->class_name]);
        $controller->pickTarget($request);
        $invAfter = $team->inventoryWithInfo( $asset->class_name , 1, $redteam->name);
        $this->assertEquals($redteam->name, $invAfter->info);
    }

    public function testPickTarget2AssetValid(){
        $controller = new BlueTeamController();
        $team = Team::factory()->create();
        $redteam = Team::factory()->red()->create();
        $redteam2 = Team::factory()->red()->create();
        $user1 = User::factory()->create(['blueteam' => $team->id, 'leader' => 1]);
        $this->be($user1);
        $user2 = User::factory()->create(['redteam' => $redteam->id]);
        $asset = new HeightenedAwarenessAsset();
        $inv = Inventory::factory()->create(['asset_name' => $asset->class_name, 'team_id' => $team->id]);
        $inv = Inventory::factory()->create(['asset_name' => $asset->class_name, 'team_id' => $team->id]);
        $request = Request::create('/picktarget','POST',['invCount' => 2, 
            'result1' => $redteam->name, 'name1' => $asset->class_name,
            'result2' => $redteam2->name, 'name2' => $asset->class_name]);
        $response = $controller->pickTarget($request);
        $invAfter = $team->inventoryWithInfo($asset->class_name, 1, $redteam->name);
        $this->assertEquals($redteam->name, $invAfter->info);
        $invAfter = $team->inventoryWithInfo($asset->class_name, 1, $redteam2->name);
        $this->assertEquals($redteam2->name, $invAfter->info);
    }

}


