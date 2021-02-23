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

class BlueMarketFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $this->be($user);
    }

    //Market Page Tests

    public function testMarketDisplaysNoTrades(){
        $blue = Auth::user()->getBlueTeam();
        $response = $this->post('/blueteam/market');
        $response->assertViewIs('blueteam.market');
        $response->assertSee($blue->name);
        $response->assertSee("There are no available trades right now.");
    }

    public function testMarketDisplaysTrades(){
        $blue = Auth::user()->getBlueTeam();
        $otherTeam = Team::factory()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $blue->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $inv2 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "AccessAudit",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $blue->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $trade2 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv2->id,
            'price' => 200
        ]);
        $response = $this->post('/blueteam/market');
        $response->assertSeeInOrder(["SQL Database", "Access Control Audit"]);
    }

    public function testMarketCannotCheckOwnTeam(){
        $blue = Auth::user()->getBlueTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $blue->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $blue->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/blueteam/market');
        $response->assertDontSee("tradeId");
        $response->assertSee("SQL Database");
    }

    //Create Trade Tests

    public function testCreateTradeNoInventory(){
        $blue = Auth::user()->getBlueTeam();
        $response = $this->post('/blueteam/createtrade');
        $response->assertViewIs('blueteam.createtrade');
        $response->assertSee("You have no assets to trade");
    }

    public function testCreateTradeWithInventory(){
        $blue = Auth::user()->getBlueTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $blue->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $this->post('/blueteam/createtrade');
        $response->assertViewIs('blueteam.createtrade');
        $response->assertSee("SQL Database");
    }

    public function testCreateTradeMissingParameters(){
        $blue = Auth::user()->getBlueTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $blue->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $this->post('/blueteam/createtrade', [
            'inv_id' => $inv1->id,
        ]);
        $response->assertViewIs('blueteam.createtrade');
        $response = $this->post('/blueteam/createtrade', [
            'price' => 100,
        ]);
        $response->assertViewIs('blueteam.createtrade');
    }

    public function testCreateTradeValid(){
        $blue = Auth::user()->getBlueTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $blue->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $this->post('/blueteam/createtrade', [
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response->assertViewIs('blueteam.market');
        $response->assertSeeInOrder([
            "SQL Database",
            "100",
            $blue->name,
        ]);
    }

    //Test Complete Trade

    public function testCompleteTradeInvalidTrade(){
        $blue = Auth::user()->getBlueTeam();
        $response = $this->post('/blueteam/market', [
            'tradeId' => 1,
        ]);
        $response->assertSee("InventoryNotFoundException");
    }

    public function testCompleteTradeNotEnoughMoney(){
        $blue = Auth::user()->getBlueTeam();
        $otherTeam = Team::factory()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/blueteam/market', [
            'tradeId' => $trade1->id,
        ]);
        $response->assertSee("Trade Not Completed");
    }

    public function testCompleteTradeValid(){
        $blue = Auth::user()->getBlueTeam();
        $blue->balance = 1000;
        $blue->update();
        $otherTeam = Team::factory()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/blueteam/market', [
            'tradeId' => $trade1->id,
        ]);
        $expectedBal = $blue->balance - 100;
        $response->assertSee($expectedBal);
        $response->assertSee("There are no available trades right now");
    }

    //Test Current Trades

    public function testCurrentTradesNoTrades(){
        $team = Auth::user()->getBlueTeam();
        $response = $this->post('/blueteam/currenttrades');
        $response->assertViewIs('blueteam.currenttrades');
        $response->assertSee($team->name);
        $response->assertSee("You have no current trades active");
    }

    public function testCurrentTradesCanSeeOwn(){
        $team = Auth::user()->getBlueTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $team->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $otherTeam = Team::factory()->create();
        $inv2 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "AdDept",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade2 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv2->id,
            'price' => 200
        ]);
        $response = $this->post('/blueteam/currenttrades');
        $response->assertViewIs('blueteam.currenttrades');
        $response->assertSee("SQL Database");
        $response->assertDontSee("Advertising Dept.");
    }

    //Test Cancel Current Trade

    public function testCancelCurrentTrade(){
        $team = Auth::user()->getBlueTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $team->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/blueteam/canceltrade',[
            'cancelTradeSubmit' => $trade1->id,
        ]);
        $response->assertViewIs('blueteam.currenttrades');
        $response->assertDontSee("SQL Database");
    }

    public function testCancelInvalidTrade(){
        $team = Auth::user()->getBlueTeam();
        $otherTeam = Team::factory()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $otherTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $response = $this->post('/blueteam/canceltrade',[
            'cancelTradeSubmit' => $trade1->id,
        ]);
        $response->assertViewIs('blueteam.currenttrades');
        $response->assertSee("Trade Not Canceled");
    }

    //Test Completed Trades

    public function testCompletedTradesNoTrades(){
        $team = Auth::user()->getBlueTeam();
        $response = $this->post('/blueteam/completedtrades');
        $response->assertViewIs('blueteam.completedtrades');
        $response->assertSee($team->name);
        $response->assertSee("You have not sold any trades");
        $response->assertSee("You have not purchased any trades");
    }

    public function testCompletedCanViewBoughtAndSold(){
        $team = Auth::user()->getBlueTeam();
        $otherTeam = Team::factory()->create();
        $trade1 = Trade::factory()->create([
            'seller_id' => $team->id,
            'buyer_id' => $otherTeam->id,
            'inv_id' => 1,
            'price' => 100,
            'asset_name' => "SQL Database",
            'asset_level' => 1,
        ]);
        $trade2 = Trade::factory()->create([
            'seller_id' => $otherTeam->id,
            'buyer_id' => $team->id,
            'inv_id' => 1,
            'price' => 100,
            'asset_name' => "Advertising Dept.",
            'asset_level' => 1,
        ]);
        $response = $this->post('/blueteam/completedtrades');
        $response->assertViewIs('blueteam.completedtrades');
        $response->assertSeeInOrder([
            "Your Sold Trades", "SQL Database",
            "Your Bought Trades", "Advertising Dept."
        ]);
    }

}
