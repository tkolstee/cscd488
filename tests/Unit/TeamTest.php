<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Inventory;
use App\Exceptions\TeamNotFoundException;
use App\Models\Assets\SQLDatabaseAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamTest extends TestCase {
    use RefreshDatabase;

    public function testGetTeamByName() {
        $team = Team::factory()->create();
        $teamRetrieve = Team::get($team->name);
        $this->assertEquals($team->name, $teamRetrieve->name);
        $this->assertEquals($team->reputation, $teamRetrieve->reputation);
        $this->assertEquals($team->balance, $teamRetrieve->balance);
        $this->assertEquals($team->blue, $teamRetrieve->blue);

        $this->expectException(TeamNotFoundException::class);
        Team::get($team->name . '123');
    }

    public function testGetBlueTeams() {
        $team1 = Team::factory()->create();
        $teams = Team::getBlueTeams();
        $this->assertEquals($team1->name, $teams->first()->name);
    }

    public function testGetNoBlueTeams() {
        $this->expectException(TeamNotFoundException::class);
        Team::getBlueTeams();
    }

    public function testGetRedTeams() {
        $team1 = Team::factory()->red()->create();
        $teams = Team::getRedTeams();
        $this->assertEquals($team1->name, $teams->first()->name);
    }

    public function testGetNoRedTeams() {
        $this->expectException(TeamNotFoundException::class);
        Team::getRedTeams();
    }

    public function testCreateBlueTeam() {
        $team = Team::createBlueTeam('name');
        $this->assertEquals($team->name, 'name');
        $this->assertEquals($team->reputation, 0);
        $this->assertEquals($team->balance, 0);
        $this->assertEquals($team->blue, 1);
    }

    public function testCreateRedTeam() {
        $team = Team::createRedTeam('name');
        $this->assertEquals($team->name, 'name');
        $this->assertEquals($team->reputation, 0);
        $this->assertEquals($team->balance, 0);
        $this->assertEquals($team->blue, 0);
    }

    public function testGetLeader() {
        $team = Team::factory()->create();
        $this->assertEmpty($team->leader());
        $user = User::factory()->create([
            'leader' => 1,
            'blueteam' => $team->id,
        ]);
        $leader = $team->leader();
        $this->assertEquals($user->name, $leader->name);
    }

    public function testGetMembers() {
        $team = Team::factory()->create();
        $this->assertEmpty($team->members());
        $user = User::factory()->create([
            'leader' => 0,
            'blueteam' => $team->id,
        ]);
        $members = $team->members();
        foreach ($members as $member){
            $this->assertEquals($user->name, $member->name);
        }
    }

    public function testInventories() {
        $team = Team::factory()->create();
        $inv = Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'SQLDatabase']);
        $inventories = $team->inventories()->first();
        $this->assertEquals($inv->team_id, $inventories->team_id);
        $this->assertEquals($inv->asset_name, $inventories->asset_name);
    }
    
    public function testInventory() {
        $team = Team::factory()->create();
        $inv = Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'SQLDatabase']);
        $asset = new SQLDatabaseAsset;
        $invReceived = $team->inventory($asset);
        $this->assertEquals($inv->quantity, $invReceived->quantity);
        $this->assertEquals($inv->team_id, $invReceived->team_id);
        $this->assertEquals($inv->asset_name, $invReceived->asset_name);
    }

    public function testSellAsset() {
        $team = Team::factory()->create();
        $inv = Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'SQLDatabase']);
        $asset = new SQLDatabaseAsset;
        $oldBalance = $team->balance;
        $this->assertTrue($team->sellAsset($asset));
        $this->assertEquals($oldBalance + $asset->purchase_cost, $team->balance);
        $inv = $team->inventory($asset);
        $this->assertNull($inv);
    }

    public function testSellAssetNotOwned() {
        $team = Team::factory()->create();
        $asset = new SQLDatabaseAsset;
        $this->assertFalse($team->sellAsset($asset));
    }

    public function testBuyAsset() {
        $team = Team::factory()->create(['balance' => 1000]);
        $asset = new SQLDatabaseAsset;
        $this->assertTrue($team->buyAsset($asset));
        $this->assertEquals(1000-$asset->purchase_cost, $team->balance);
    }

    public function testBuyAssetNotEnoughMoney() {
        $team = Team::factory()->create(['balance' => 0]);
        $asset = new SQLDatabaseAsset;
        $this->assertFalse($team->buyAsset($asset));
        $team = $team->fresh();
        $this->assertEquals(0, $team->balance);
    }

    public function testSetName() {
        $team = Team::factory()->create();
        $this->assertTrue($team->setName('newName'));
        $this->assertEquals('newName', $team->name);
    }

    public function testSetNameTaken() {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();
        $this->assertFalse($team1->setName($team2->name));
        $this->assertNotEquals($team1->name, $team2->name);
    }
}
