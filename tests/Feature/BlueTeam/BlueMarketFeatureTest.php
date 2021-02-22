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
        $response->assertSeeInOrder(["SQL Database", "Access Audit"]);
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

}
