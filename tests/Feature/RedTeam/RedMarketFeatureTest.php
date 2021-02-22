<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Game;
use App\Models\Trade;
use App\Models\Inventory;
use App\Exceptions\InventoryNotFoundException;
use Tests\TestCase;
use Auth;

class RedMarketFeatureTest extends TestCase
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

    //Market Page Tests

    public function testMarketDisplaysNoTrades(){
        $red = Auth::user()->getRedTeam();
        $response = $this->post('/redteam/market');
        $response->assertViewIs('redteam.market');
        $response->assertSee($red->name);
        $response->assertSee("There are no available trades right now.");
    }

    public function testMarketDisplaysTrades(){
        $red = Auth::user()->getRedTeam();
        $otherTeam = Team::factory()->red()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $red->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $inv2 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "VPN",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $red->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $trade2 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv2->id,
            'price' => 200
        ]);
        $response = $this->post('/redteam/market');
        $response->assertSeeInOrder(["Botnet", "Virtual Private Network"]);
    }

    public function testMarketCannotCheckOwnTeam(){
        $red = Auth::user()->getRedTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $red->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $red->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/redteam/market');
        $response->assertDontSee("tradeId");
        $response->assertSee("Botnet");
    }

    //Create Trade Tests

    public function testCreateTradeNoInventory(){
        $red = Auth::user()->getRedTeam();
        $response = $this->post('/redteam/createtrade');
        $response->assertViewIs('redteam.createtrade');
        $response->assertSee("You have no assets to trade");
    }

    public function testCreateTradeWithInventory(){
        $red = Auth::user()->getRedTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $red->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $this->post('/redteam/createtrade');
        $response->assertViewIs('redteam.createtrade');
        $response->assertSee("Botnet");
    }

    public function testCreateTradeMissingParameters(){
        $red = Auth::user()->getRedTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $red->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $this->post('/redteam/createtrade', [
            'inv_id' => $inv1->id,
        ]);
        $response->assertViewIs('redteam.createtrade');
        $response = $this->post('/redteam/createtrade', [
            'price' => 100,
        ]);
        $response->assertViewIs('redteam.createtrade');
    }

    public function testCreateTradeValid(){
        $red = Auth::user()->getRedTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $red->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $this->post('/redteam/createtrade', [
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response->assertViewIs('redteam.market');
        $response->assertSeeInOrder([
            "Botnet",
            "100",
            $red->name,
        ]);
    }

    //Test Complete Trade

    public function testCompleteTradeInvalidTrade(){
        $red = Auth::user()->getRedTeam();
        $response = $this->post('/redteam/market', [
            'tradeId' => 1,
        ]);
        $response->assertSee("InventoryNotFoundException");
    }

    public function testCompleteTradeNotEnoughMoney(){
        $red = Auth::user()->getRedTeam();
        $otherTeam = Team::factory()->red()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/redteam/market', [
            'tradeId' => $trade1->id,
        ]);
        $response->assertSee("Trade Not Completed");
    }

    public function testCompleteTradeValid(){
        $red = Auth::user()->getRedTeam();
        $red->balance = 1000;
        $red->update();
        $otherTeam = Team::factory()->red()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/redteam/market', [
            'tradeId' => $trade1->id,
        ]);
        $expectedBal = $red->balance - 100;
        $response->assertSee($expectedBal);
        $response->assertSee("There are no available trades right now");
    }

}
