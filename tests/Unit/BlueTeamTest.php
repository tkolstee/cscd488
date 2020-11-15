<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Http\Controllers\BlueTeamController;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Inventory;
use App\Models\Asset;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;


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

    public function testDeleteValidBlueTeam(){
        $team = Team::factory()->make();
        $team->save();
        $controller = new BlueTeamController();
        $request = Request::create('/delete', 'POST', [
            'name' => $team->name,
        ]);
        $controller->delete($request);
        $this->assertTrue(Team::all()->where('name', '=', $team->name)->isEmpty());
    }

    public function testDeleteInvalidBlueTeam(){
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $controller = new BlueTeamController();
        $this->expectException(TeamNotFoundException::class);
        $controller->delete($request);
    }

    public function testJoinValidBlueTeam(){
        $controller = new BlueTeamController();
        $team = Team::factory()->make();
        $team->save();
        $request = Request::create('/join', 'POST', [
            'result' => $team->name,
        ]);
        $controller->join($request);
        $this->assertNotEquals(Auth::user()->blueteam, "");
        $this->assertEquals(0, Auth::user()->leader);
    }

    public function testJoinInvalidBlueTeam(){
        $controller = new BlueTeamController();
        $request = Request::create('/join', 'POST', [
            'result' => 'invalid name',
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->join($request);
    }

    public function testBlueBuyValidAsset(){
        $asset = Asset::factory()->create();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $blueteam = Team::find(Auth::user()->blueteam);
        $balanceBefore = $blueteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-($asset->purchase_cost), $response->blueteam->balance);
        $this->assertEquals(1, $inventory->quantity);
    }

    public function testBuyAlreadyOwned(){
        $blueteam = $this->assignTeam();
        $asset = Asset::factory()->create();
        $inventory = Inventory::factory()->create([
            'asset_id' => $asset->id,
            'team_id' => $blueteam->id,
            'quantity' => 1
        ]);
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $quantBefore = Inventory::all()->where('team_id','=',$blueteam->id)->first()->quantity;
        $balanceBefore = $blueteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balanceBefore-$asset->purchase_cost, $response->blueteam->balance);
        $this->assertEquals($quantBefore + 1, $inventory->quantity);
    }

    public function testBuyInvalidAssetName(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['InvalidName']
        ]);
        $this->expectException(AssetNotFoundException::class);
        $controller->buy($request);
    }

    public function testInvalidBlueTeamCannotBuy(){
        $asset = Asset::factory()->create();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->buy($request);
    }

    public function testBlueTeamBuyNotEnoughMoney(){
        $asset = Asset::factory()->create();
        $blueteam = $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $blueteam->balance = 0;
        $blueteam->update();
        $response = $controller->buy($request);
        $this->assertEquals('not-enough-money', $response->error);
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

    public function testSellItemOwnedOneValid(){
        $asset = Asset::factory()->create();
        $blueteam = $this->assignTeam();
        $inventory = Inventory::factory()->create([
            'asset_id' => $asset->id,
            'team_id' => $blueteam->id,
            'quantity' => 1,
        ]);
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
        ]);
        $balBefore = $blueteam->balance;
        $response = $controller->sell($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balBefore+$asset->purchase_cost, $response->blueteam->balance);
        $this->assertTrue($inventory == null);
    }

    public function testSellItemOwnedManyValid(){
        $asset = Asset::factory()->create();
        $blueteam = $this->assignTeam();
        $inventory = Inventory::factory()->create([
            'asset_id' => $asset->id,
            'team_id' => $blueteam->id,
            'quantity' => 5,
        ]);
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
        ]);
        $balBefore = $blueteam->balance;
        $quantBefore = $inventory->quantity;
        $response = $controller->sell($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balBefore+$asset->purchase_cost, $response->blueteam->balance);
        $this->assertEquals($quantBefore - 1, $inventory->quantity);
    }

    public function testBlueSellMultipleItems(){
        $blueteam = $this->assignTeam();
        $asset1 = Asset::factory()->create();
        $inventory1 = Inventory::factory()->create([
            'asset_id' => $asset1->id,
            'team_id' => $blueteam->id,
            'quantity' => 3,
        ]);
        $asset2 = Asset::factory()->create();
        $inventory2 = Inventory::factory()->create([
            'asset_id' => $asset2->id,
            'team_id' => $blueteam->id,
            'quantity' => 5
        ]);

        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset1->name, $asset2->name]
        ]);
        $balanceBefore = $blueteam->balance;
        $qtyBefore1 = $inventory1->quantity;
        $qtyBefore2 = $inventory2->quantity;

        $controller->sell($request);
        $inventory1 = Inventory::find($inventory1->id);
        $inventory2 = Inventory::find($inventory2->id);
        $blueteam = Team::find($blueteam->id);
        $this->assertEquals($qtyBefore1-1, $inventory1->quantity);
        $this->assertEquals($qtyBefore2-1, $inventory2->quantity);
        $expectedBalance = $balanceBefore + $asset1->purchase_cost + $asset2->purchase_cost;
        $this->assertEquals($expectedBalance, $blueteam->balance);
    }

    public function testSellItemNotOwned(){
        $asset = Asset::factory()->create();
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
        ]);
        $this->expectException(InventoryNotFoundException::class);
        $controller->sell($request);
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
            'results' => ['invalidName']
        ]);
        $this->expectException(AssetNotFoundException::class);
        $controller->sell($request);
    }

    public function testSellInvalidTeam(){
        $asset = Asset::factory()->create();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->sell($request);
    }

    public function testDisplayTeamMembersNoTeam(){
        $controller = new BlueTeamController();
        $response = $controller->home();
        $this->assertTrue(empty($response->blueteam));
        $this->assertTrue(empty($response->leader));
        $this->assertTrue(empty($response->members));
    }

    public function testDisplayTeamLeaderValid(){
        $blueteam = $this->assignTeam();
        $controller = new BlueTeamController();
        $response = $controller->home();
        $username = Auth::user()->name;
        $leader = $response->leader;
        $this->assertEquals($blueteam->name, $response->blueteam->name);
        $leadername = $leader->name;
        $this->assertEquals($username, $leadername);
        $this->assertTrue($response->members->isEmpty());
    }

    public function testDisplayTeamMembersValid(){
        $blueteam = $this->assignTeam();
        $member1 = User::factory()->create([
            'blueteam' => $blueteam->id,
        ]);
        $member2 = User::factory()->create([
            'blueteam' => $blueteam->id,
        ]);
        $controller = new BlueTeamController();
        $response = $controller->home();
        $this->assertEquals($blueteam->name, $response->blueteam->name);
        $this->assertEquals(Auth::user()->name, $response->leader->name);
        $this->assertEquals(2,count($response->members));
    }

}
