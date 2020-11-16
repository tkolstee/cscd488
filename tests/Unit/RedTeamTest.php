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
        $controller->create($request);
    }

    public function testDeleteValidRedTeam(){
        $team = Team::factory()->red()->make();
        $team->save();
        $controller = new RedTeamController();
        $request = Request::create('/delete', 'POST', [
            'name' => $team->name,
        ]);
        $controller->delete($request);
        $this->assertTrue(Team::all()->where('name', '=', $team->name)->isEmpty());
    }

    public function testDeleteInvalidRedTeam(){
        $request = Request::create('/delete', 'POST', [
            'name' => 'test',
        ]);
        $controller = new RedTeamController();
        $this->expectException(TeamNotFoundException::class);
        $controller->delete($request);
    }

    public function testRedBuyValidAsset(){
        $asset = Asset::factory()->create();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $redteam = Team::find(Auth::user()->redteam);
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find(1);
        $this->assertEquals($balanceBefore-($asset->purchase_cost), $response->redteam->balance);
        $this->assertEquals(1, $inventory->quantity);
    }

    public function testBuyAlreadyOwned(){
        $redteam = $this->assignTeam();
        $asset = Asset::factory()->create();
        $inventory = Inventory::factory()->create([
            'asset_id' => $asset->id,
            'team_id' => $redteam->id,
            'quantity' => 1
        ]);
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $quantBefore = Inventory::all()->where('team_id','=',$redteam->id)->first()->quantity;
        $balanceBefore = $redteam->balance;
        $response = $controller->buy($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balanceBefore-$asset->purchase_cost, $response->redteam->balance);
        $this->assertEquals($quantBefore + 1, $inventory->quantity);
    }

    public function testBuyInvalidAssetName(){
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => ['InvalidName']
        ]);
        $this->expectException(AssetNotFoundException::class);
        $controller->buy($request);
    }

    public function testInvalidRedTeamCannotBuy(){
        $asset = Asset::factory()->create();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->buy($request);
    }

    public function testRedTeamBuyNotEnoughMoney(){
        $asset = Asset::factory()->create();
        $redteam = $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$asset->name]
        ]);
        $redteam->balance = 0;
        $redteam->update();
        $response = $controller->buy($request);
        $this->assertEquals('not-enough-money', $response->error);
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

    public function testSellItemOwnedOneValid(){
        $asset = Asset::factory()->create();
        $redteam = $this->assignTeam();
        $inventory = Inventory::factory()->create([
            'asset_id' => $asset->id,
            'team_id' => $redteam->id,
            'quantity' => 1,
        ]);
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
        ]);
        $balBefore = $redteam->balance;
        $response = $controller->sell($request);
        $inventory = Inventory::find($inventory->id);
        $this->assertEquals($balBefore+$asset->purchase_cost, $response->redteam->balance);
        $this->assertTrue($inventory == null);
    }

    public function testSellItemOwnedManyValid(){
        $asset = Asset::factory()->create();
        $redteam = $this->assignTeam();
        $inventory = Inventory::factory()->create([
            'asset_id' => $asset->id,
            'team_id' => $redteam->id,
            'quantity' => 5,
        ]);
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
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
        $asset1 = Asset::factory()->create();
        $inventory1 = Inventory::factory()->create([
            'asset_id' => $asset1->id,
            'team_id' => $redteam->id,
            'quantity' => 3,
        ]);
        $asset2 = Asset::factory()->create();
        $inventory2 = Inventory::factory()->create([
            'asset_id' => $asset2->id,
            'team_id' => $redteam->id,
            'quantity' => 5
        ]);

        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset1->name, $asset2->name]
        ]);
        $balanceBefore = $redteam->balance;
        $qtyBefore1 = $inventory1->quantity;
        $qtyBefore2 = $inventory2->quantity;

        $controller->sell($request);
        $inventory1 = Inventory::find($inventory1->id);
        $inventory2 = Inventory::find($inventory2->id);
        $redteam = Team::find($redteam->id);
        $this->assertEquals($qtyBefore1-1, $inventory1->quantity);
        $this->assertEquals($qtyBefore2-1, $inventory2->quantity);
        $expectedBalance = $balanceBefore + $asset1->purchase_cost + $asset2->purchase_cost;
        $this->assertEquals($expectedBalance, $redteam->balance);
    }

    public function testSellItemNotOwned(){
        $asset = Asset::factory()->red()->create();
        $this->assignTeam();
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
        ]);
        $this->expectException(InventoryNotFoundException::class);
        $controller->sell($request);
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
            'results' => ['invalidName']
        ]);
        $this->expectException(AssetNotFoundException::class);
        $controller->sell($request);
    }

    public function testSellInvalidTeam(){
        $asset = Asset::factory()->red()->create();
        $controller = new RedTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$asset->name]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $controller->sell($request);
    }

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
        $this->assertNotNull($response->possibleAttacks);
        $this->assertNotNull($response->uselessPossibleAttacks);
    }

}
