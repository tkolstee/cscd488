<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Attack;
use App\Models\Asset;
use App\Models\Bonus;
use App\Models\Game;
use Tests\TestCase;
use App\Models\Inventory;
use App\Models\Assets\SQLDatabaseAsset;

class BlueStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $user = User::factory()->create();
        $this->be($user);
    }

    //Store Tests

    public function testBlueTeamCanViewAssetsInStore()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $response = $this->actingAs($user)->get('/blueteam/store');
        $response->assertViewIs('blueteam.store');
        $response->assertSee("Access Control Audit");
    }

    public function testBlueFilterInStore(){
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $response = $this->actingAs($user)->post('/blueteam/filter');
        $response->assertViewIs('blueteam.store');
        $response->assertSee(["Access Control Audit", "Advertising Dept."]);
        $response = $this->actingAs($user)->post('/blueteam/filter',[
            'filter' => "Action"
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSee("Access Control Audit");
        $response->assertDontSee("Advertising Dept.");
        $response = $this->actingAs($user)->post('/blueteam/filter',[
            'filter' => "No Filter",
            'sort' => "purchase_cost"
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertDontSee("Access Control Audit");
    }

    //Buy Tests

    public function testBlueTeamCanAddToCart()
    {
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $results = [];
        $results += ["Firewall" => 1];
        $response = $this->actingAs($user)->post('/blueteam/buy', [
            'results' => $results,
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSeeInOrder(["Firewall", 1]);
    }

    public function testBlueTeamCanAddMultipleToCart()
    {
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id
        ]);
        $results = [];
        $results += ["Firewall" => 3];
        $response = $this->actingAs($user)->post('/blueteam/buy', [
            'results' => $results,
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSeeInOrder(["Firewall", 3]);
    }

    public function testBlueTeamCanBuyAssets()
    {
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $results = [];
        $results += ["Firewall" => 1];
        $this->actingAs($user)->post('/blueteam/buy', [
            'results' => $results,
        ]);
        $expectedBalance = $team->balance - Asset::get("Firewall")->purchase_cost;
        $response = $this->actingAs($user)->get('/blueteam/endturn');
        $response->assertViewIs('blueteam.home');
        $response->assertSee('Revenue: ' . $expectedBalance);
    }

    //Inventory Tests

    public function testBlueTeamCanViewAssetsInInventory()
    {
        $asset = Asset::getBuyableBlue()[0];
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $this->be($user);
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

    public function testBlueCanViewInventoryButtons(){
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $this->be($user);
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

    //Sell Tests

    public function testBlueAddToSellCart(){
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $this->be($user);
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
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $this->be($user);
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

    public function testBlueCanSellAssets(){ // still fails
        $team = Team::factory()->create();
        $team->balance = 1000;
        $team->update();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $this->be($user);
        $asset = new SQLDatabaseAsset();
        $inv = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 1,
        ]);
        session(['sellCart' => $inv->id]);
        $response = $this->get('/blueteam/endturn');
        $expectedBalance = $team->balance + $asset->purchase_cost;
        $response->assertViewIs('blueteam.home');
        $response->assertSeeInOrder(['Revenue', $expectedBalance]);
        }

}