<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Attack;
use App\Models\Inventory;
use App\Exceptions\TeamNotFoundException;

use Illuminate\Foundation\Testing\RefreshDatabase;

class BonusTest extends TestCase {
    use RefreshDatabase;
    
    private function createTeams(){
        $user = User::factory()->create();
        $blueteam = Team::factory()->create();
        $redteam = Team::factory()->red()->create();
        $user->redteam = $redteam->id;
        $user->update();
        $this->be($user);
        return $redteam;
    }

    private function createBonus($teamID, $tags){
        $bonus = Bonus::factory()->create();
        $bonus->team_id = $teamID;
        $bonus->target_id = 1; //works because blueteam was made first, id is 1..
        $bonus->tags = $tags;
        $bonus->update();
        return $bonus;
    }

    public function testBonusCreateValid(){
        $team = $this->createTeams();
        $bonus = Bonus::createBonus($team->id, []);
        $this->assertEquals($team->id, $bonus->team_id);
        $this->assertEmpty($bonus->tags);
        $bonus = Bonus::createBonus($team->id, ["RevenueDeduction"]);
        $this->assertEquals($team->id, $bonus->team_id);
        $this->assertEquals(1, count($bonus->tags));
        $this->assertEquals("RevenueDeduction", $bonus->tags[0]);
    }

    public function testCheckDelete(){
        $team = $this->createTeams();
        $bonus = Bonus::createBonus($team->id, []);
        $bonus->checkDelete();
        $this->assertEmpty(Bonus::all());
        $bonus = Bonus::createBonus($team->id, []);
        $bonus->percentDiffDeducted = 5;
        $bonus->update();
        $bonus->checkDelete();
        $this->assertEquals(1, count(Bonus::all()));
    }

    public function testTurnChangeDeductions(){
        $team = $this->createTeams();
        $tags = ["RevenueDeduction", "ReputationDeduction", "DetectionDeduction", "AnalysisDeduction", "DifficultyDeduction"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->percentRevDeducted = 10;
        $bonus->percentRepDeducted = 10;
        $bonus->percentDetDeducted = 10;
        $bonus->percentAnalDeducted = 10;
        $bonus->percentDiffDeducted = 10;
        $bonus->update();
        $bonus->onTurnChange();
        $this->assertEquals([5,5,5,5,5],[$bonus->percentRevDeducted, $bonus->percentRepDeducted, $bonus->percentDetDeducted, $bonus->percentAnalDeducted, $bonus->percentDiffDeducted]);
        $bonus->onTurnChange();
        $this->assertEmpty(Bonus::all());
    }

    public function testTurnChangeAddTokens(){
        $team = $this->createTeams();
        $tags = ['AddTokens'];
        $bonus = $this->createBonus($team->id, $tags);

        $this->assertEmpty(Inventory::all());
        $bonus->onTurnChange();
        $inv = Inventory::all()->first();
        $this->assertEquals(1, $inv->quantity);

        $bonus->onTurnChange();
        $inv->refresh();
        $this->assertEquals(2, $inv->quantity);
    }

    public function testTurnChangeTokensCappedAt5(){
        $team = $this->createTeams();
        $tags = ['AddTokens'];
        $bonus = $this->createBonus($team->id, $tags);

        $bonus->onTurnChange();
        $inv = Inventory::all()->first();
        $inv->quantity = 5;
        $inv->update();

        $bonus->onTurnChange();
        $inv->refresh();
        $this->assertEquals(5, $inv->quantity);
    }

    public function testTurnChangeSteal(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $blueteam->update();
        $inv = Inventory::factory()->create(['team_id' => $blueteam->id, 'asset_name' => 'AdDept']);
        $blueteam->balance = 1000;
        $blueteam->update();
        $team->balance = 0;
        $team->update();
        $tags = ["RevenueSteal"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->target_id = $blueteam->id;
        $bonus->percentRevStolen = 10;
        $bonus->update();
        $this->assertTrue(in_array("RevenueSteal", $bonus->tags));
        $this->assertEquals($team->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $amount = $bonus->onTurnChange();
        $team = Team::find($team->id);
        $blueteam = Team::find($blueteam->id);
        $this->assertEquals(995, $blueteam->balance);
        $this->assertEquals(5, $team->balance);
    }

    public function testTurnChangeOneTurn(){
        $team = $this->createTeams();
        $bonus = $this->createBonus($team->id, ["OneTurnOnly"]);
        $bonus->onTurnChange();
        $this->assertEmpty(Bonus::all());
    }

    public function testDifficultyReductionOnPreAttack(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $tags = ["DifficultyDeduction"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->percentDiffDeducted = 50;
        $bonus->target_id = $blueteam->id;
        $bonus->update();
        $attack = Attack::create('SynFlood', $team->id, $blueteam->id);
        $diffBefore = $attack->calculated_success_chance;
        $attack->onPreAttack();
        $this->assertTrue($attack->possible);
        $this->assertEquals($diffBefore * 0.5, $attack->calculated_success_chance);
    }

    public function testChanceToRemove(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $tags = ["ChanceToRemove"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->removalChance = 0;
        $bonus->target_id = $blueteam->id;
        $bonus->update();
        
        $this->assertNotEmpty(Bonus::all());

        $bonus->onTurnChange();
        $this->assertNotEmpty(Bonus::all());

        $bonus->removalChance = 100;
        $bonus->onTurnChange();
        $this->assertEmpty(Bonus::all());
    }

    public function testChanceToRemoveAndDeduction(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $tags = ["ChanceToRemove", "RevenueDeduction"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->removalChance = 0;
        $bonus->percentRevDeducted = 0;
        $bonus->target_id = $blueteam->id;
        $bonus->update();

        $bonus->onTurnChange();
        $this->assertNotEmpty(Bonus::all());
    }

    public function testPayToRemoveBonusIncorrectTags(){
        $bonus = new Bonus;
        $bonus->tags = [];
        $this->assertFalse($bonus->payToRemove());
    }

    public function testPayToRemoveNotEnoughMoney(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $blueteam->balance = -10; //Impossible, but works for testing purposes
        $blueteam->update();
        $tags = ["PayToRemove"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->removalCostFactor = 1;
        $bonus->update();

        $this->assertFalse($bonus->payToRemove());
        $this->assertNotEmpty(Bonus::all());
    }

    public function testPayToRemoveValidBonus(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $removalFactor = 3;
        $cost = $blueteam->getPerTurnRevenue() * $removalFactor;
        $blueteam->balance = $cost;
        $blueteam->update();
        $tags = ["PayToRemove"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->removalCostFactor = $removalFactor;

        $bonus->payToRemove();
        $this->assertEmpty(Bonus::all());
        $blueteam->refresh();
        $this->assertEquals(0, $blueteam->balance);
        $team->refresh();
        $this->assertEquals($cost, $team->balance);
    }

    public function testRevenueGenerationOnTurnChange(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $tags = ['RevenueGeneration'];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->revenueGenerated = 100;
        $bonus->update();

        $this->assertEquals(0, $team->balance);
        $bonus->onTurnChange();
        $team->refresh();
        $this->assertEquals(100, $team->balance);
        $bonus->onTurnChange();
        $team->refresh();
        $this->assertEquals(200, $team->balance);
    }
}