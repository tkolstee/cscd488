<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Attack;
use App\Models\Asset;
use App\Models\Game;
use Tests\TestCase;
use App\Models\Inventory;
use App\Models\Assets\SQLDatabaseAsset;
use Auth;

class BlueInventoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $this->be($user);
    }

    //Inventory Tests

    public function testBlueTeamCanViewAssetsInInventory()
    {
        $asset = Asset::getBuyableBlue()[0];
        $team = Auth::user()->getBlueTeam();
        $response = $this->get('/blueteam/inventory');
        $response->assertSee("You have no assets.");
        Inventory::factory()->create([
            'asset_name' => $asset->name,
            'team_id' => $team->id,
            'quantity' => 5,
        ]);
        $response = $this->get('/blueteam/inventory');
        $response->assertSeeInOrder([$asset->name, "5"]);
    }

    //Sell Tests

    public function testBlueAddToSellCart(){
        $team = Auth::user()->getBlueTeam();
        $asset = new SQLDatabaseAsset();
        $inv = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $results = [];
        $results += [$inv->id => 1];
        $response = $this->post('/blueteam/sell',[
            'results' => $results
        ]);
        $response->assertViewIs('blueteam.inventory');
        $response->assertSeeInOrder(["Sell Cart", $asset->name, 1]);
    }

    public function testBlueAddMultipleToSellCart(){
        $team = Auth::user()->getBlueTeam();
        $asset = new SQLDatabaseAsset();
        $inv = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 5,
        ]);
        $results = [];
        $results += [$inv->id => 3];
        $response = $this->post('/blueteam/sell',[
            'results' => $results
        ]);
        $response->assertViewIs('blueteam.inventory');
        $response->assertSeeInOrder(["Sell Cart", $asset->name, 3]);
    }

    public function testBlueCanSellAssets(){
        $team = Auth::user()->getBlueTeam();
        $asset = new SQLDatabaseAsset();
        $inv = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        session(['sellCart' => [$inv->id]]);
        $response = $this->get('/blueteam/endturn');
        $expectedBalance = $team->balance + $asset->purchase_cost;
        $response->assertViewIs('blueteam.home');
        $response->assertSeeInOrder(['Revenue', $expectedBalance]);
    }

    public function testBlueCanCancelSellCart(){
        $team = Auth::user()->getBlueTeam();
        $asset = new SQLDatabaseAsset;
        $inv = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        session(['sellCart' => [$asset->name]]);
        $response = $this->post('/blueteam/cancel',[
            'cart' => "sell",
            'cancel' => $inv->id
        ]);
        $response->assertViewIs('blueteam.inventory');
        $cart = session('sellCart');
        $this->assertEmpty($cart);
    }

    //Asset Button tests

    public function testBlueCanViewInventoryButtons(){
        $team = Auth::user()->getBlueTeam();
        //Pick target button
        $inv = Inventory::factory()->create([
            'asset_name' => "HeightenedAwareness",
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $response = $this->get('/blueteam/inventory');
        $response->assertSeeInOrder(["Heightened Awareness", "Pick Target"]);
        Inventory::destroy($inv->id);
        //Use Action button
        $inv = Inventory::factory()->create([
            'asset_name' => "AccessControlAudit",
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $response = $this->get('/blueteam/inventory');
        $response->assertSeeInOrder(["Access Control Audit", "Use"]);
        Inventory::destroy($inv->id);
        //Upgrade Button
        $inv = Inventory::factory()->create([
            'asset_name' => "SQLDatabase",
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $response = $this->get('/blueteam/inventory');
        $response->assertSeeInOrder(["SQL Database", "Upgrade"]);
    }

    public function testPickTargetButton(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'asset_name' => "HeightenedAwareness",
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $response = $this->post('/blueteam/picktarget', [
            'submit' => $inv->id
        ]);
        $response->assertViewIs('blueteam.target');
        $response->assertSee("There are no teams to target right now.");
        $targetTeam = Team::factory()->red()->create();
        $response = $this->post('/blueteam/picktarget', [
            'submit' => $inv->id
        ]);
        $response->assertViewIs('blueteam.target');
        $response->assertSee($targetTeam->name);
    }

    public function testPickTargetForm(){
        $team = Auth::user()->getBlueTeam();
        $targetTeam = Team::factory()->red()->create();
        $inv = Inventory::factory()->create([
            'asset_name' => "HeightenedAwareness",
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $response = $this->post('/blueteam/picktarget', [
            'invCount' => 1,
            'result1' => $targetTeam->name,
            'name1' => "HeightenedAwareness",
        ]);
        $response->assertViewIs('blueteam.inventory');
    }

    public function testUseActionButton(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'asset_name' => "AccessAudit",
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $response = $this->post('/asset', [
            'submit' => $inv->asset_name
        ]);
        $response->assertViewIs('blueteam.home');
        $response->assertSeeInOrder(["You managed to remove", "access tokens that were targeting your team"]);
    }

    public function testUpgradeButton(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'asset_name' => "SQLDatabase",
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        $response = $this->post('/blueteam/upgrade', [
            'submit' => $inv->id
        ]);
        $response->assertViewIs('blueteam.inventory');
        $response->assertSeeInOrder(["SQL Database", "Level: 2", "Upgrade"]);
        $inv = $team->inventories()->first();
        $response = $this->post('/blueteam/upgrade', [
            'submit' => $inv->id
        ]);
        $response->assertViewIs('blueteam.inventory');
        $response->assertSeeInOrder(["SQL Database", "Level: 3"]);
        $response->assertDontSee("Upgrade");
    }

}