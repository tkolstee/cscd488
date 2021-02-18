<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Trade;
use App\Models\Inventory;
use App\Exceptions\InventoryNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Auth;

class TradeTest extends TestCase {
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
        ]);
        $this->be($user);
    }

    //Create Tests

    public function testTradeCreateTrade(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = Trade::createTrade($team->id, $inv->id, 100);
        $this->assertEquals($team->id, $response->seller_id);
        $this->assertEquals($inv->id, $response->inv_id);
        $this->assertEquals(100, $response->price);
        $this->assertNull($response->buyer_id);
    }

    public function testBlueTeamCreateTradeValid(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $team->createTrade($inv->id, 100);
        $this->assertEquals($team->id, $response->seller_id);
        $this->assertEquals($inv->id, $response->inv_id);
        $this->assertEquals(100, $response->price);
        $this->assertNull($response->buyer_id);
    }

    public function testRedTeamCreateTradeValid(){
        $team = Team::factory()->red()->create();
        $user = Auth::user();
        $user->redteam = $team->id;
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "Botnet",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $team->createTrade($inv->id, 100);
        $this->assertEquals($team->id, $response->seller_id);
        $this->assertEquals($inv->id, $response->inv_id);
        $this->assertEquals(100, $response->price);
        $this->assertNull($response->buyer_id);
    }

    public function testTeamCannotTradeInvalidInventory(){
        $team = Auth::user()->getBlueTeam();
        $this->expectException(InventoryNotFoundException::class);
        $response = $team->createTrade(1, 100);
        $team2 = Team::factory()->create();
        $inv = Inventory::factory()->create([
            'team_id' => $team2->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $team->createTrade($inv->id, 100);
    }

    public function testTeamCannotCreateTradeNoPrice(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $team->createTrade($inv->id, null);
        $this->assertFalse($response);
    }

    public function testTeamCanCreateTargetedTradeNoInfo(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "HeightenedAwareness",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $team->createTrade($inv->id, 100);
        $this->assertEquals($team->id, $response->seller_id);
        $this->assertEquals($inv->id, $response->inv_id);
        $this->assertEquals(100, $response->price);
        $this->assertNull($response->buyer_id);
    }

    public function testTeamCanNotCreateTargetedTradeWithInfo(){
        $team = Auth::user()->getBlueTeam();
        $redteam = Team::factory()->red()->create();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "HeightenedAwareness",
            'level' => 1,
            'quantity' => 1,
            'info' => $redteam->name
        ]);
        $response = $team->createTrade($inv->id, 100);
        $this->assertFalse($response);
    }

    public function testTeamCanNotCreateTradeNotBuyable(){
        $team = Team::factory()->red()->create();
        $team2 = Team::factory()->create();
        $user = Auth::user();
        $user->redteam = $team->id;
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "PhysicalAccess",
            'level' => 1,
            'quantity' => 1,
            'info' => $team2->name,
        ]);
        $response = $team->createTrade($inv->id, 100);
        $this->assertFalse($response);
    }

    //Get Trade Tests

    public function testTeamGetCurrentTrades(){
        $team = Auth::user()->getBlueTeam();
        $inv1 = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $inv2 = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "AccessAudit",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = new Trade;
        $trade1->seller_id = $team->id;
        $trade1->inv_id = $inv1->id;
        $trade1->price = 100;
        $trade1->save();
        $trade2 = new Trade;
        $trade2->seller_id = $team->id;
        $trade2->inv_id = $inv2->id;
        $trade2->price = 200;
        $trade2->save();
        $currentTrades = $team->getCurrentTrades();
        $this->assertEquals(2, count($currentTrades));
        $firstTrade = $currentTrades->pop();
        $this->assertEquals($inv2->id, $firstTrade->inv_id);
        $secondTrade = $currentTrades->pop();
        $this->assertEquals($inv1->id, $secondTrade->inv_id);
    }

    public function testTeamGetCompletedTrades(){
        $team = Auth::user()->getBlueTeam();
        $redteam = Team::factory()->red()->create();
        $inv1 = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $inv2 = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "AccessAudit",
            'level' => 1,
            'quantity' => 1,
        ]);
        $trade1 = new Trade;
        $trade1->seller_id = $team->id;
        $trade1->buyer_id = $redteam->id;
        $trade1->inv_id = $inv1->id;
        $trade1->price = 100;
        $trade1->save();
        $trade2 = new Trade;
        $trade2->seller_id = $team->id;
        $trade2->buyer_id = $redteam->id;
        $trade2->inv_id = $inv2->id;
        $trade2->price = 200;
        $trade2->save();
        $completedTrades = $team->getCompletedTrades();
        $this->assertEquals(2, count($completedTrades));
        $firstTrade = $completedTrades->pop();
        $this->assertEquals($inv2->id, $firstTrade->inv_id);
        $secondTrade = $completedTrades->pop();
        $this->assertEquals($inv1->id, $secondTrade->inv_id);
    }

}