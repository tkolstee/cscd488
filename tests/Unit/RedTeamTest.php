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


class RedTeamTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $user = User::factory()->create();
        $this->be($user);
    }

    public function assignTeam(){
        $team = Team::factory()->red()->create([
            'balance' => 1000,
        ]);
        $user = Auth::user();
        $user->redteam = $team->id;
        $user->update();
        return $team;
    }
    
    //Create Tests
    //Should Return create view if name empty
    //Validate the name is unique
    //Return home

    public function testCreateValidRedTeam(){
        $request = Request::create('/create', 'POST', [
            'name' => 'test',
        ]);
        $controller = new RedTeamController();
        $response = $controller->create($request);
        $this->assertEquals($response->redteam->id, Auth::user()->redteam);
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
        $controller->create($request);
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
        $request = Request::create('/sell','POST',[
            'results' => [$inventory->id]
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
        $request = Request::create('/sell','POST',[
            'results' => [$inventory->id]
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
        $request = Request::create('/sell','POST',[
            'results' => [$inventory1->id, $inventory1->id]
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
        $request = Request::create('/sell','POST',[
            'results' => ["Invalid"]
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
        $request = Request::create('/sell','POST',[
            'results' => ["invalidName"]
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

    //ChooseAttack Tests
    //Should error if no result
    //return choose attack with redteam,blueteam and possibleattacks

    public function testChooseAttackValidTeam(){
        $redteam = $this->assignTeam();
        $blueteam = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/chooseattack','POST',[
            'result' => $blueteam->name
        ]);
        $response = $controller->chooseAttack($request);
        $this->assertEquals($redteam->name, $response->redteam->name);
        $this->assertEquals($blueteam->name, $response->blueteam->name);
        $this->assertFalse(empty($response->possibleAttacks));
    }

    public function testChooseAttackNoTeam(){
        $blueteam = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/chooseattack','POST',[
            'result' => $blueteam->name
        ]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->chooseAttack($request);
    }

    public function testChooseAttackNoResults(){
        $redteam = $this->assignTeam();
        $blueteam = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/chooseattack','POST',[
            'result' => ""
        ]);
        $response = $controller->chooseAttack($request);
        $this->assertEquals("No-Team-Selected", $response->error);
    }

    //PerformAttackTests
    //Should error if no result
    //Create the attack and call onPreAttack
    //return home if attack isn'tpossible
    //return minigame view with attack, redteam, blueteam

    public function testPerformAttackNoTeam(){
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SynFlood"]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->performAttack($request);
    }

    public function testPerformAttackNoResult(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', []);
        $response = $controller->performAttack($request);
        $this->assertEquals("No-Attack-Selected", $response->error);
    }

    public function testPerformAttackPossible(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SynFlood"]);
        $response = $controller->performAttack($request);
        $this->assertEquals($team->id, $response->redteam->id);
        $this->assertEquals($team->id, $response->attack->redteam);
        $this->assertEquals($target->id, $response->attack->blueteam);
        $this->assertFalse(empty($response->attack->difficulty));
        $this->assertFalse(empty($response->attack->detection_risk));
        $this->assertTrue($response->attack->possible);
        $this->assertEquals("Syn Flood", $response->attack->name);
    }

    public function testPerformAttackNoPrereqs(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SQLInjection"]);
        $response = $controller->performAttack($request);
        $this->assertEquals("Unsatisfied prereqs for this attack", $response->attMsg);
    }

    public function testPerformAttackNoEnergy(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $target = Team::factory()->create();
        $team->setEnergy(0);
        $request = Request::create('/performattack','POST', ['blueteam' => $target->name, 'result' => "SynFlood"]);
        $response = $controller->performAttack($request);
        $this->assertEquals("Not enough energy available.", $response->attMsg);
    }

    //Settings Tests
    //Should return view with redteam,changeName,and leaveTeam

    public function testSettingsNoParamValid(){
        $controller = new RedTeamController();
        $redteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[]);
        $response = $controller->settings($request);
        $this->assertEquals($redteam->id, $response->redteam->id);
        $this->assertFalse($response->changeName);
        $this->assertFalse($response->leaveTeam);
    }

    public function testSettingsNoTeamThrows(){
        $controller = new RedTeamController();
        $request = Request::create('/settings','POST',[]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->settings($request);
    }

    public function testSettingsChangeNameValid(){
        $controller = new RedTeamController();
        $redteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[
           'changeNameBtn' => 1, 
        ]);
        $response = $controller->settings($request);
        $this->assertEquals($redteam->id, $response->redteam->id);
        $this->assertTrue($response->changeName);
        $this->assertFalse($response->leaveTeam);
    }

    public function testSettingsLeaveTeamValid(){
        $controller = new RedTeamController();
        $redteam = $this->assignTeam();
        $request = Request::create('/settings','POST',[
           'leaveTeamBtn' => 1, 
        ]);
        $response = $controller->settings($request);
        $this->assertEquals($redteam->id, $response->redteam->id);
        $this->assertFalse($response->changeName);
        $this->assertTrue($response->leaveTeam);
    }

    //ChangeName tests
    //Should throw if no team
    //Error if name taken
    //Change name if available return

    public function testChangeNameNoTeam(){
        $controller = new RedTeamController();
        $request = Request::create('/changename','POST',['name' => 'newName']);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->changeName($request);
    }

    public function testChangeNameNameTaken(){
        $this->assignTeam();
        $team2 = Team::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/changename','POST',['name' => $team2->name]);
        $response = $controller->changeName($request);
        $this->assertEquals("name-taken", $response->error);
    }

    public function testChangeNameValid(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/changename','POST',['name' =>"new name"]);
        $response = $controller->changeName($request);
        $this->assertEquals("new name", Auth::user()->getRedTeam()->name);
    }

    //LeaveTeam tests
    //Should return to settings if stay
    //Error if not leave
    //Leaves team 

    public function testLeaveTeamNoTeam(){
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "stay"]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->leaveTeam($request);
    }

    public function testLeaveTeamBadOption(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "invalid"]);
        $response = $controller->leaveTeam($request);
        $this->assertEquals("invalid-option", $response->error);
    }

    public function testLeaveTeamStay(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "stay"]);
        $response = $controller->leaveTeam($request);
        $this->assertEquals($team->id, Auth::user()->getRedTeam()->id);
    }

    public function testLeaveTeamValid(){
        $team = $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/leaveteam', 'POST', ['result' => "leave"]);
        $response = $controller->leaveTeam($request);
        $this->assertNull(Auth::user()->redteam);
        $this->assertNull(Team::find($team->id));
    }

}
