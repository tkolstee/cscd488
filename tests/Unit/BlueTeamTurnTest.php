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
use App\Models\Blueteam;
use View;
use Auth;
use Exception;

class BlueTeamTurnTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $this->login();
        $this->assignTeam();
        $this->prefillAssets();
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
        $blueteam = new Blueteam();
        $blueteam->team_id = $teamid;
        $blueteam->save();
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

    public function testEndTurnNoSessionValid(){
        $controller = new BlueTeamController();
        $response = $controller->endTurn();
        $turnTakenAfter = Blueteam::all()->where('team_id','=',Auth::user()->blueteam)->first()->turn_taken;
        $this->assertEquals(1, $turnTakenAfter);
    }

    public function testEndTurnBuyOneValid(){
        $controller = new BlueTeamController();
        $teamID = Auth::user()->blueteam;
        $asset = Asset::find(1);
        $cart[] = $asset->name;
        session(['cart' => $cart]);
        $balBefore = Team::find($teamID)->balance;
        $response = $controller->endTurn();
        $turnTakenAfter = Blueteam::all()->where('team_id','=', $teamID)->first()->turn_taken;
        $this->assertEquals(1, $turnTakenAfter);
        $balAfter = Team::find($teamID)->balance;
        $this->assertEquals($balBefore - $asset->purchase_cost, $balAfter);
        $invAfter = Inventory::all()->where('team_id','=', $teamID)->where('asset_id','=',1)->first();
        $this->assertEquals(1, $invAfter->quantity);
    }

    public function testEndTurnBuyOneAlreadyOwnedValid(){
        $controller = new BlueTeamController();
        $teamID = Auth::user()->blueteam;
        $asset = Asset::find(1);
        $cart[] = $asset->name;
        session(['cart' => $cart]);
        $balBefore = Team::find($teamID)->balance;
        $this->buyAssets();
        $invBefore = Inventory::all()->where('team_id','=', $teamID)->where('asset_id','=',1)->first();
        $response = $controller->endTurn();
        $turnTakenAfter = Blueteam::all()->where('team_id','=', $teamID)->first()->turn_taken;
        $this->assertEquals(1, $turnTakenAfter);
        $balAfter = Team::find($teamID)->balance;
        $this->assertEquals($balBefore - $asset->purchase_cost, $balAfter);
        $invAfter = Inventory::all()->where('team_id','=', $teamID)->where('asset_id','=',1)->first();
        $this->assertEquals($invBefore->quantity + 1, $invAfter->quantity);
    }

    public function testEndTurnBuyOneNotEnoughMoney(){
        $controller = new BlueTeamController();
        $teamID = Auth::user()->blueteam;
        $team = Team::find($teamID);
        $team->balance = 0;
        $team->update();
        $asset = Asset::find(1);
        $cart[] = $asset->name;
        session(['cart' => $cart]);
        $response = $controller->endTurn();
        $this->assertEquals("not-enough-money", $response->error);
    }

    public function testEndTurnSellOneValid(){
        $controller = new BlueTeamController();
        $teamID = Auth::user()->blueteam;
        $asset = Asset::find(1);
        $cart[] = -1;
        $cart[] = $asset->name;
        session(['cart' => $cart]);
        $balBefore = Team::find($teamID)->balance;
        $this->buyAssets();
        $response = $controller->endTurn();
        $turnTakenAfter = Blueteam::all()->where('team_id','=', $teamID)->first()->turn_taken;
        $this->assertEquals(1, $turnTakenAfter);
        $balAfter = Team::find($teamID)->balance;
        $this->assertEquals($balBefore + $asset->purchase_cost, $balAfter);
        $invAfter = Inventory::all()->where('team_id','=', $teamID)->where('asset_id','=',1)->first();
        $this->assertNull($invAfter);
    }

    public function testEndTurnSellOneNotOwned(){
        $controller = new BlueTeamController();
        $teamID = Auth::user()->blueteam;
        $asset = Asset::find(1);
        $cart[] = -1;
        $cart[] = $asset->name;
        session(['cart' => $cart]);
        $this->expectException(Exception::class);
        $response = $controller->endTurn();
    }

    public function testEndTurnSellOwnManyValid(){
        $controller = new BlueTeamController();
        $teamID = Auth::user()->blueteam;
        $asset = Asset::find(1);
        $cart[] = -1;
        $cart[] = $asset->name;
        session(['cart' => $cart]);
        $balBefore = Team::find($teamID)->balance;
        $this->buyManyAssets();
        $invBefore = Inventory::all()->where('team_id','=', $teamID)->where('asset_id','=',1)->first();
        $response = $controller->endTurn();
        $turnTakenAfter = Blueteam::all()->where('team_id','=', $teamID)->first()->turn_taken;
        $this->assertEquals(1, $turnTakenAfter);
        $balAfter = Team::find($teamID)->balance;
        $this->assertEquals($balBefore + $asset->purchase_cost, $balAfter);
        $invAfter = Inventory::all()->where('team_id','=', $teamID)->where('asset_id','=',1)->first();
        $this->assertEquals($invBefore->quantity - 1, $invAfter->quantity);
    }

    public function testEndTurnSellAndBuy(){
        $controller = new BlueTeamController();
        $teamID = Auth::user()->blueteam;
        $asset = Asset::find(1);
        $cart[] = $asset->name;
        $cart[] = -1;
        $cart[] = $asset->name;
        session(['cart' => $cart]);
        $balBefore = Team::find($teamID)->balance;
        $response = $controller->endTurn();
        $turnTakenAfter = Blueteam::all()->where('team_id','=', $teamID)->first()->turn_taken;
        $this->assertEquals(1, $turnTakenAfter);
        $balAfter = Team::find($teamID)->balance;
        $this->assertEquals($balBefore, $balAfter);
        $invAfter = Inventory::all()->where('team_id','=', $teamID)->where('asset_id','=',1)->first();
        $this->assertNull($invAfter);
    }

}
