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
use App\Models\Inventory;
use App\Models\Asset;
use View;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;


class BlueTeamTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $this->login();
    }

    private function login(){
        $user = User::factory()->make();
        $user->save();
        $this->be($user);
    }

    private function assignTeam(){
        $user = Auth::user();
        $team = Team::factory()->make();
        $team->balance = 1000;
        $team->save();
        $teamid = substr(Team::all()->where('name','=',$team->name)->pluck('id'),1,1);
        $user->blueteam = $teamid;
        $user->leader = 1;
        $user->update();
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
        $this->expectException(TeamNotFoundException::class);
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
        $this->assertNotEquals(Auth::user()->blueteam, "");
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

    public function testBlueBuyValidAsset(){
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
        $this->expectException(AssetNotFoundException::class);
        $response = $controller->buy($request);
    }

    public function testInvalidBlueTeamCannotBuy(){
        $assetName = $this->prefillAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->buy($request);
    }

    public function testBlueTeamBuyNotEnoughMoney(){
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
        $this->expectException(InventoryNotFoundException::class);
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
        $this->expectException(AssetNotFoundException::class);
        $response = $controller->sell($request);
    }

    public function testSellInvalidTeam(){
        $assetName = $this->prefillAssets();
        $controller = new BlueTeamController();
        $request = Request::create('/sell','POST',[
            'results' => [$assetName]
        ]);
        $this->expectException(TeamNotFoundException::class);
        $response = $controller->sell($request);
    }

    public function testDisplayTeamMembersNoTeam(){
        $controller = new BlueTeamController();
        $response = $controller->home();
        $this->assertTrue(empty($response->blueteam));
        $this->assertTrue(empty($response->leader));
        $this->assertTrue(empty($response->members));
    }

    public function testDisplayTeamLeaderValid(){
        $this->assignTeam();
        $controller = new BlueTeamController();
        $response = $controller->home();
        $username = Auth::user()->name;
        $leader = $response->leader;
        $this->assertEquals(Team::find(1)->name, $response->blueteam->name);
        $leadername = $leader->name;
        $this->assertEquals($username, $leadername);
        $this->assertTrue($response->members->isEmpty());
    }

    private function fillTeam(){
        $teamid = Auth::user()->blueteam;
        $member1 = User::factory()->make();
        $member1->blueteam = $teamid;
        $member2 = User::factory()->make();
        $member2->blueteam = $teamid;
        $member1->save();
        $member2->save();
    }

    public function testDisplayTeamMembersValid(){
        $this->assignTeam();
        $this->fillTeam();
        $controller = new BlueTeamController();
        $response = $controller->home();
        $this->assertEquals(Team::find(1)->name, $response->blueteam->name);
        $this->assertEquals(Auth::user()->name, $response->leader->name);
        $this->assertEquals(2,count($response->members));
    }

}
