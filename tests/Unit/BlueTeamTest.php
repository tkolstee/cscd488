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
        $response = $controller->join($request);
    }

    public function testBlueBuyValidAsset(){
        $asset = Asset::factory()->create();
        $assetName = $asset->name;
        $this->assignTeam();
        $controller = new BlueTeamController();
        $request = Request::create('/buy','POST', [
            'results' => [$assetName]
        ]);
        $result = $controller->buy($request);
        $buyCart = session('buyCart');
        $this->assertEquals(1, count($buyCart));
        $this->assertEquals($assetName, $buyCart[0]);
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
        $response = $controller->sell($request);
        $sellCart = session('sellCart');
        $this->assertEquals(1, count($sellCart));
        $this->assertEquals($asset->name, $sellCart[0]);
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
}
