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

    public function testDiffOnPre(){
        $team = $this->createTeams();
        $blueteam = Team::all()->where('blue','=',1)->first();
        $tags = ["DifficultyDeduction"];
        $bonus = $this->createBonus($team->id, $tags);
        $bonus->percentDiffDeducted = .5;
        $bonus->target_id = $blueteam->id;
        $bonus->update();
        $attack = Attack::create('SynFlood', $team->id, $blueteam->id);
        $diffBefore = $attack->calculated_difficulty;
        $attack->onPreAttack();
        $this->assertTrue($attack->possible);
        $this->assertEquals($diffBefore - 1, $attack->calculated_difficulty);
        
    }

}