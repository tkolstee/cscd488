<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Trade;
use App\Models\Inventory;
use App\Exceptions\TeamNotFoundException;
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

    public function testTeamCannotCreateTradeNegativePrice(){
        $team = Auth::user()->getBlueTeam();
        $inv = Inventory::factory()->create([
            'team_id' => $team->id,
            'asset_name' => "SQLDatabase",
            'level' => 1,
            'quantity' => 1,
        ]);
        $response = $team->createTrade($inv->id, -100);
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
        $team2 = Team::factory()->create();
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
        $trade1 = Trade::factory()->create([
            'seller_id' => $team->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $trade2 = Trade::factory()->create([
            'seller_id' => $team->id,
            'inv_id' => $inv2->id,
            'price' => 200
        ]);
        $completedTrade = Trade::factory()->create([
            'seller_id' => $team->id,
            'inv_id' => $inv2->id,
            'buyer_id' => $team2->id,
            'price' => 100
        ]);
        $currentTrades = $team->getCurrentTrades();
        $this->assertEquals(2, count($currentTrades));
        $firstTrade = $currentTrades->pop();
        $this->assertEquals($inv2->id, $firstTrade->inv_id);
        $secondTrade = $currentTrades->pop();
        $this->assertEquals($inv1->id, $secondTrade->inv_id);
    }

    public function testTeamGetSoldTrades(){
        $team = Auth::user()->getBlueTeam();
        $team2 = Team::factory()->create();
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
        $trade1 = Trade::factory()->create([
            'seller_id' => $team->id,
            'buyer_id' => $team2->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $trade2 = Trade::factory()->create([
            'seller_id' => $team->id,
            'buyer_id' => $team2->id,
            'inv_id' => $inv2->id,
            'price' => 100
        ]);
        $currentTrade = Trade::factory()->create([
            'seller_id' => $team->id,
            'inv_id' => $inv2->id,
            'price' => 100
        ]);
        $completedTrades = $team->getSoldTrades();
        $this->assertEquals(2, count($completedTrades));
        $firstTrade = $completedTrades->pop();
        $this->assertEquals($inv2->id, $firstTrade->inv_id);
        $secondTrade = $completedTrades->pop();
        $this->assertEquals($inv1->id, $secondTrade->inv_id);
    }

    public function testTeamGetBoughtTrades(){
        $team = Auth::user()->getBlueTeam();
        $team2 = Team::factory()->create();
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
        $trade1 = Trade::factory()->create([
            'seller_id' => $team->id,
            'buyer_id' => $team2->id,
            'inv_id' => $inv1->id,
            'price' => 100
        ]);
        $trade2 = Trade::factory()->create([
            'seller_id' => $team->id,
            'buyer_id' => $team2->id,
            'inv_id' => $inv2->id,
            'price' => 100
        ]);
        $currentTrade = Trade::factory()->create([
            'seller_id' => $team->id,
            'inv_id' => $inv2->id,
            'price' => 100
        ]);
        $completedTrades = $team2->getBoughtTrades();
        $this->assertEquals(2, count($completedTrades));
        $firstTrade = $completedTrades->pop();
        $this->assertEquals($inv2->id, $firstTrade->inv_id);
        $secondTrade = $completedTrades->pop();
        $this->assertEquals($inv1->id, $secondTrade->inv_id);
    }

    //Complete Trade Tests

    public function testCompleteTradeBasicValid(){
        $sellTeam = Team::factory()->create();
        $buyTeam = Auth::user()->getBlueTeam();
        $buyTeam->balance = 10000;
        $buyTeam->update();
        $expectedBuyBal = $buyTeam->balance - 100;
        $expectedSellBal = $sellTeam->balance + 100;
        $sellInv = Inventory::factory()->create([
            'team_id' => $sellTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 1,
            'info' => null
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $response = $buyTeam->completeTrade($trade->id);
        $this->assertEquals($trade->id, $response->id);
        $this->assertEquals($trade->seller_id, $response->seller_id);
        $this->assertEquals(100, $response->price);
        $this->assertEquals($buyTeam->id, $response->buyer_id);
        //Assert Charged Team and Gave Team money
        $buyTeam->fresh();
        $sellTeam = Team::find($sellTeam->id);
        $this->assertEquals($expectedBuyBal, $buyTeam->balance);
        $this->assertEquals($expectedSellBal, $sellTeam->balance);
        //Assert Inventories Changed
        $this->assertEquals(1, count($buyTeam->inventories()));
        $this->assertEmpty($sellTeam->inventories());
        //Assert Trade in Bought and Sold Trades
        $boughtTrades = $buyTeam->getBoughtTrades();
        $this->assertEquals(1, count($boughtTrades));
        $boughtTrade = $boughtTrades->first();
        $this->assertEquals($trade->id, $boughtTrade->id);
        $soldTrades = $sellTeam->getSoldTrades();
        $this->assertEquals(1, count($soldTrades));
        $soldTrade = $soldTrades->first();
        $this->assertEquals($trade->id, $soldTrade->id);
    }

    public function testCompleteTradeWithInfoValid(){
        $sellTeam = Team::factory()->create();
        $buyTeam = Auth::user()->getBlueTeam();
        $buyTeam->balance = 10000;
        $buyTeam->update();
        $expectedBuyBal = $buyTeam->balance - 100;
        $expectedSellBal = $sellTeam->balance + 100;
        $sellInv = Inventory::factory()->create([
            'team_id' => $sellTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 1,
            'info' => "Information"
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $response = $buyTeam->completeTrade($trade->id);
        $this->assertEquals($trade->id, $response->id);
        $this->assertEquals($trade->seller_id, $response->seller_id);
        $this->assertEquals(100, $response->price);
        $this->assertEquals($buyTeam->id, $response->buyer_id);
        //Assert Charged Team and Gave Team money
        $buyTeam->fresh();
        $sellTeam = Team::find($sellTeam->id);
        $this->assertEquals($expectedBuyBal, $buyTeam->balance);
        $this->assertEquals($expectedSellBal, $sellTeam->balance);
        //Assert Inventories Changed
        $this->assertEquals(1, count($buyTeam->inventories()));
        $this->assertEquals("Information", $buyTeam->inventories()->first()->info);
        $this->assertEmpty($sellTeam->inventories());
        //Assert Trade in Bought and Sold Trades
        $boughtTrades = $buyTeam->getBoughtTrades();
        $this->assertEquals(1, count($boughtTrades));
        $boughtTrade = $boughtTrades->first();
        $this->assertEquals($trade->id, $boughtTrade->id);
        $soldTrades = $sellTeam->getSoldTrades();
        $this->assertEquals(1, count($soldTrades));
        $soldTrade = $soldTrades->first();
        $this->assertEquals($trade->id, $soldTrade->id);
    }

    public function testCompleteTradeSellerOwnsMultipleValid(){
        $sellTeam = Team::factory()->create();
        $buyTeam = Auth::user()->getBlueTeam();
        $buyTeam->balance = 10000;
        $buyTeam->update();
        $expectedBuyBal = $buyTeam->balance - 100;
        $expectedSellBal = $sellTeam->balance + 100;
        $sellInv = Inventory::factory()->create([
            'team_id' => $sellTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 3,
            'info' => null
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $response = $buyTeam->completeTrade($trade->id);
        $this->assertEquals($trade->id, $response->id);
        $this->assertEquals($trade->seller_id, $response->seller_id);
        $this->assertEquals(100, $response->price);
        $this->assertEquals($buyTeam->id, $response->buyer_id);
        //Assert Charged Team and Gave Team money
        $buyTeam->fresh();
        $sellTeam = Team::find($sellTeam->id);
        $this->assertEquals($expectedBuyBal, $buyTeam->balance);
        $this->assertEquals($expectedSellBal, $sellTeam->balance);
        //Assert Inventories Changed
        $buyInv = $buyTeam->inventories();
        $this->assertEquals(1, count($buyInv));
        $this->assertEquals(1, $buyInv->first()->quantity);
        $sellInv = $sellTeam->inventories();
        $this->assertEquals(1, count($sellInv));
        $this->assertEquals(2, $sellInv->first()->quantity);
        //Assert Trade in Bought and Sold Trades
        $boughtTrades = $buyTeam->getBoughtTrades();
        $this->assertEquals(1, count($boughtTrades));
        $boughtTrade = $boughtTrades->first();
        $this->assertEquals($trade->id, $boughtTrade->id);
        $soldTrades = $sellTeam->getSoldTrades();
        $this->assertEquals(1, count($soldTrades));
        $soldTrade = $soldTrades->first();
        $this->assertEquals($trade->id, $soldTrade->id);
    }

    public function testCompleteTradeBuyerAlreadyOwnsValid(){
        $sellTeam = Team::factory()->create();
        $buyTeam = Auth::user()->getBlueTeam();
        $buyTeam->balance = 10000;
        $buyTeam->update();
        $expectedBuyBal = $buyTeam->balance - 100;
        $expectedSellBal = $sellTeam->balance + 100;
        $sellInv = Inventory::factory()->create([
            'team_id' => $sellTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 1,
            'info' => null
        ]);
        $buyInv = Inventory::factory()->create([
            'team_id' => $buyTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 1,
            'info' => null
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $response = $buyTeam->completeTrade($trade->id);
        $this->assertEquals($trade->id, $response->id);
        $this->assertEquals($trade->seller_id, $response->seller_id);
        $this->assertEquals(100, $response->price);
        $this->assertEquals($buyTeam->id, $response->buyer_id);
        //Assert Charged Team and Gave Team money
        $buyTeam->fresh();
        $sellTeam = Team::find($sellTeam->id);
        $this->assertEquals($expectedBuyBal, $buyTeam->balance);
        $this->assertEquals($expectedSellBal, $sellTeam->balance);
        //Assert Inventories Changed
        $buyInv = $buyTeam->inventories();
        $this->assertEquals(1, count($buyInv));
        $this->assertEquals(2, $buyInv->first()->quantity);
        $this->assertEmpty($sellTeam->inventories());
        //Assert Trade in Bought and Sold Trades
        $boughtTrades = $buyTeam->getBoughtTrades();
        $this->assertEquals(1, count($boughtTrades));
        $boughtTrade = $boughtTrades->first();
        $this->assertEquals($trade->id, $boughtTrade->id);
        $soldTrades = $sellTeam->getSoldTrades();
        $this->assertEquals(1, count($soldTrades));
        $soldTrade = $soldTrades->first();
        $this->assertEquals($trade->id, $soldTrade->id);
    }

    public function testCompleteTradeWithInfoTargetedInvalid(){
        $sellTeam = Team::factory()->red()->create();
        $buyTeam = Team::factory()->red()->create();
        $blueTeam = Team::factory()->create();
        Auth::user()->redteam = $buyTeam->id;
        Auth::user()->update();
        $buyTeam->balance = 10000;
        $buyTeam->update();
        $sellInv = Inventory::factory()->create([
            'team_id' => $sellTeam->id,
            'asset_name' => "HeightenedAwareness",
            'level' => 2,
            'quantity' => 1,
            'info' => $blueTeam->name
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $this->expectException(InventoryNotFoundException::class);
        $response = $buyTeam->completeTrade($trade->id);
    }

    public function testCompleteTradeInvalidTrade(){
        $ownerTeam = Team::factory()->create();
        $sellTeam = Team::factory()->create();
        $buyTeam = Auth::user()->getBlueTeam();
        $buyTeam->balance = 10000;
        $buyTeam->update();
        $sellInv = Inventory::factory()->create([
            'team_id' => $ownerTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 1,
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $this->expectException(InventoryNotFoundException::class);
        $response = $buyTeam->completeTrade($trade->id);
        $response = $buyTeam->completeTrade($trade->id + 1);
    }

    public function testCompleteTradeNotEnoughMoney(){
        $sellTeam = Team::factory()->create();
        $buyTeam = Auth::user()->getBlueTeam();
        $buyTeam->balance = 0;
        $buyTeam->update();
        $sellInv = Inventory::factory()->create([
            'team_id' => $sellTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 1,
            'info' => null
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $response = $buyTeam->completeTrade($trade->id);
        $this->assertFalse($response);
    }

    public function testCompleteTradeInvalidSeller(){
        $sellTeam = Team::factory()->red()->create();
        $buyTeam = Auth::user()->getBlueTeam();
        $buyTeam->balance = 1000;
        $buyTeam->update();
        $sellInv = Inventory::factory()->create([
            'team_id' => $sellTeam->id,
            'asset_name' => "SQLDatabase",
            'level' => 2,
            'quantity' => 1,
            'info' => null
        ]);
        $trade = Trade::factory()->create([
            'seller_id' => $sellTeam->id,
            'inv_id' => $sellInv->id,
            'price' => 100,
        ]);
        $this->expectException(TeamNotFoundException::class);
        $response = $buyTeam->completeTrade($trade->id);
    }

}