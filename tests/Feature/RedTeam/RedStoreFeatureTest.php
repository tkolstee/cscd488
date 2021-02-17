<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Assets\FirewallAsset;
use App\Models\Game;
use App\Models\Inventory;
use Tests\TestCase;
use Auth;

class RedStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $team = Team::factory()->red()->create();
        $user = User::factory()->create([
            'redteam' => $team->id,
        ]);
        $this->be($user);
    }

    //Store + Buy Tests

    public function testRedCanViewAssetsInStore()
    {
        $asset = Asset::getBuyableRed()[0];
        $response = $this->get('/redteam/store');
        $response->assertViewIs('redteam.store');
        $response->assertSee($asset->name);
    }

    /*
    public function testRedFilterInStore(){
        $assets = Asset::getBuyableRed();
        $response = $this->post('/redteam/filter');
        $response->assertViewIs('redteam.store');
        $response->assertSee($assets[0]->name);
        $response = $this->post('/redteam/filter',[
            'filter' => "offensive tag 1"
        ]);
        $response->assertViewIs('redteam.store');
        $response->assertSee($assets[0]->name);
        $response = $this->post('/redteam/filter',[
            'filter' => "No Filter",
            'sort' => "purchase_cost"
        ]);
        $response->assertViewIs('redteam.store');
    }
    */

    public function testRedCanBuyAssets()
    {
        $asset = Asset::getBuyableRed()[0];
        $team = Auth::user()->getRedTeam();
        $team->balance = 1000;
        $team->update();
        $results = [];
        $results += [$asset->class_name => 1];
        $expectedBalance = $team->balance - $asset->purchase_cost;
        $response = $this->post('/redteam/buy', [
            'results' => $results,
        ]);
        $response->assertViewIs('redteam.store');
        $response->assertSee('Cash: ' . $expectedBalance);
    }

    public function testRedCanBuyMultipleAssets()
    {
        $asset = new FirewallAsset;
        $team = Auth::user()->getRedTeam();
        $team->balance = 1000;
        $team->update();
        $results = [];
        $results += [$asset->class_name => 3];
        $expectedBalance = $team->balance - (3 * $asset->purchase_cost);
        $response = $this->post('/redteam/buy', [
            'results' => $results,
        ]);
        $response->assertViewIs('redteam.store');
        $response->assertSee('Cash: ' . $expectedBalance);
    }

    public function testRedCannotBuyWithNoMoney()
    {
        $asset = Asset::getBuyableRed()[0];
        $team = Auth::user()->getRedTeam();
        $team->balance = 0;
        $team->update();
        $expectedBalance = $team->balance;
        $results = [];
        $results += [$asset->class_name => 1];
        $response = $this->post('/redteam/buy', [
            'results' => $results,
        ]);
        $response->assertViewIs('redteam.store');
        $response->assertSee('Cash: ' . $expectedBalance);
        $response->assertSee("not-enough-money");
    }

    //Inventory + Sell Tests

    public function testRedCanViewAssetsInInventory()
    {
        $asset = Asset::getBuyableRed()[0];
        $team = Auth::user()->getRedTeam();
        $response = $this->get('/redteam/inventory');
        $response->assertSee("You have no assets.");
        Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 5,
        ]);
        $response = $this->get('/redteam/inventory');
        $response->assertSeeInOrder([$asset->name, "5"]);
    }

    public function testRedCanSell(){
        $asset = Asset::getBuyableRed()[0];
        $team = Auth::user()->getRedTeam();
        $inv = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 5,
        ]);
        $results = [];
        $results += [$inv->id => 1];
        $response = $this->post('/redteam/sell',[
            'results' => $results
        ]);
        $response->assertViewIs('redteam.inventory');
        $response->assertSeeInOrder([
            $asset->name, 4
        ]);
    }

    public function testRedCanSellMultiple(){
        $asset = Asset::getBuyableRed()[0];
        $team = Auth::user()->getRedTeam();
        $inv = Inventory::factory()->create([
            'asset_name' => $asset->class_name,
            'team_id' => $team->id,
            'quantity' => 5,
        ]);
        $results = [];
        $results += [$inv->id => 3];
        $response = $this->post('/redteam/sell',[
            'results' => $results
        ]);
        $response->assertViewIs('redteam.inventory');
        $response->assertSeeInOrder([
            $asset->name, 2
        ]);
    }

    public function testRedCannotSellNoneOwned(){
        $asset = Asset::getBuyableRed()[0];
        $team = Auth::user()->getRedTeam();
        $results = [];
        $results += [1 => 3];
        $response = $this->post('/redteam/sell',[
            'results' => $results
        ]);
        $response->assertViewIs('redteam.inventory');
        $response->assertSee("not-enough-owned");
    }

}
