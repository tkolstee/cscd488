<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Attack;
use App\Models\Inventory;
use App\Exceptions\TeamNotFoundException;
use App\Models\Assets\FirewallAsset;
use App\Models\Assets\SQLDatabaseAsset;
use App\Models\Assets\AccessTokenAsset;
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
        $invReceived = $team->inventory($asset, 1);
        $this->assertEquals($inv->quantity, $invReceived->quantity);
        $this->assertEquals($inv->team_id, $invReceived->team_id);
        $this->assertEquals($inv->asset_name, $invReceived->asset_name);
        $this->assertEquals(1, $invReceived->level);
    }

    public function testGetAssets() {
        $team = Team::factory()->create();
        Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'SQLDatabase']);
        Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'Firewall']);
        $assets = $team->assets();
        $this->assertEquals(new SQLDatabaseAsset, $assets[0]);
        $this->assertEquals(new FirewallAsset, $assets[1]);
        $this->assertEquals(2, $assets->count());
    }

    public function testSellAsset() {
        $team = Team::factory()->create();
        $inv = Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'SQLDatabase']);
        $asset = new SQLDatabaseAsset;
        $oldBalance = $team->balance;
        $this->assertTrue($team->sellInventory($inv));
        $this->assertEquals($oldBalance + $asset->purchase_cost, $team->balance);
        $inv = $team->inventory($asset, 1);
        $this->assertNull($inv);
    }

    public function testSellAssetNotOwned() {
        $team = Team::factory()->create();
        $team2 = Team::factory()->create();
        $asset = new SQLDatabaseAsset;
        $inv = Inventory::factory()->create(['team_id' => $team2->id, 'asset_name' => 'SQLDatabase']);
        $this->assertFalse($team->sellInventory($inv));
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

    public function testGetAccessTokens(){
        $team = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $asset = new AccessTokenAsset();
        $token = Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => $asset->class_name, 'info' => $blue->name]);
        $getTokens = $team->getTokens();
        $this->assertEquals("AccessToken", $getTokens->first()->asset_name);
        $this->assertEquals(1, count($getTokens));
    }

    public function testAddAccessToken(){
        $team = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $team->addToken($blue->name, 1);
        $token = Inventory::all()->where('team_id', '=', $team->id)->where('asset_name', '=', 'AccessToken')->
            where('info', '=', $blue->name)->first();
        $this->assertNotNull($token);
        $this->assertEquals(1, $token->level);
        $this->assertEquals($blue->name, $token->info);
        $this->assertEquals(1, $token->quantity);
    }

    public function testRemoveAccessTokenQuantity1(){
        $team = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $token = Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'AccessToken', 'info' => $blue->name]);
        $team->removeToken($blue->name, 1);
        $tokenAfter = Inventory::all()->where('team_id', '=', $team->id)->where('asset_name', '=', 'AccessToken')->
            where('info', '=', $blue->name)->first();
        $this->assertNull($tokenAfter);
    }

    public function testRemoveAccessTokenQuantityMany(){
        $team = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $token = Inventory::factory()->many()->create(['team_id' => $team->id, 'asset_name' => 'AccessToken', 'info' => $blue->name]);
        $team->removeToken($blue->name, 1);
        $tokenAfter = Inventory::all()->where('team_id', '=', $team->id)->where('asset_name', '=', 'AccessToken')->
            where('info', '=', $blue->name)->first();
        $this->assertEquals($token->quantity - 1, $tokenAfter->quantity);
    }

    public function testTeamHasAnalyst(){
        $team = Team::factory()->create();
        $this->assertFalse($team->hasAnalyst());
        Inventory::factory()->create(['team_id' => $team->id, 'asset_name' => 'SecurityAnalyst']);
        $this->assertTrue($team->hasAnalyst());
    }


    public function testDaysSinceLastAttackNoAttacks(){
        $team = Team::factory()->create();
        $this->assertEquals(0, $team->daysSinceLastAttack());
        $team = Team::find(1);
        $team->created_at = $team->created_at->subDays(4);
        $team->update();
        $this->assertEquals(4, $team->daysSinceLastAttack());
    }

    public function testDaysSinceLastAttack(){
        $blue = Team::factory()->create();
        $red = Team::factory()->red()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack->success = true;
        Attack::updateAttack($attack);
        $this->assertEquals(0, $blue->daysSinceLastAttack());

        $attack = Attack::find(1);
        $attack->created_at = $attack->created_at->subDays(4);
        $attack->update();
        $this->assertEquals(4, $blue->daysSinceLastAttack());
    }
}
