<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attack;
use App\Models\Team;
use App\Models\Inventory;
use App\Exceptions\AttackNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Models\Attacks\MalvertiseAttack;
use App\Models\Attacks\SQLInjectionAttack;
use App\Models\Attacks\SynFloodAttack;
use App\Models\Assets\AccessTokenAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttackTest extends TestCase {
    use RefreshDatabase;

    public function testGetAllAttacks() {
        $attacks = Attack::getAll();
        $this->assertNotEquals(0, count($attacks));
    }

    public function testGetLearnableAttacks() {
        $attacks = Attack::getLearnableAttacks();
        $this->assertNotEquals(0, count($attacks));
        foreach ($attacks as $attack) {
            $this->assertTrue($attack->learn_page);
        }
    }

    public function testGetAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = new Attack;
        $attack->class_name = 'SQLInjection';
        $attack->redteam = $red->id;
        $attack->blueteam = $blue->id;
        $attack->save();
        $retrievedAttack = Attack::get('SQLInjection', $red->id, $blue->id);
        $this->assertEquals(SQLInjectionAttack::class, get_class($retrievedAttack));
    }

    public function testGetInvalidAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = new Attack;
        $attack->class_name = 'NotAnAttack';
        $attack->redteam = $red->id;
        $attack->blueteam = $blue->id;
        $attack->save();
        $this->expectException(AttackNotFoundException::class);
        Attack::get('NotAnAttack', $red->id, $blue->id);
    }

    public function testGetRedTeamPreviousAttacks() {
        $red = Team::factory()->red()->create();
        $diffRed = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $this->assertEmpty(Attack::getRedPreviousAttacks($red->id));

        Attack::create('SynFlood', $red->id, $blue->id);
        $attack1 = Attack::get('SynFlood', $red->id, $blue->id);
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack2 = Attack::get('SQLInjection', $red->id, $blue->id);
        Attack::create('SynFlood', $diffRed->id, $blue->id);

        $prevAttacks = Attack::getRedPreviousAttacks($red->id);
        $this->assertEquals(2, $prevAttacks->count());
        $prevAttack1 = $prevAttacks[0];
        $this->assertEquals($attack1->class_name, $prevAttack1->class_name);
        $this->assertEquals($red->id, $prevAttack1->redteam);
        $prevAttack2 = $prevAttacks[1];
        $this->assertEquals($attack2->class_name, $prevAttack2->class_name);
        $this->assertEquals($red->id, $prevAttack2->redteam);
    }

    public function testGetBlueTeamPreviousAttacks() {
        $red = Team::factory()->red()->create();
        $diffBlue = Team::factory()->create();
        $blue = Team::factory()->create();
        $this->assertEmpty(Attack::getBluePreviousAttacks($blue->id));

        $attack1 =  Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        Attack::updateAttack($attack1);
        $attack2 = Attack::create('SQLInjection', $red->id, $diffBlue->id);
        $attack2->detection_level = 1;
        Attack::updateAttack($attack2);

        $prevAttacks = Attack::getBluePreviousAttacks($blue->id);
        $this->assertEquals(1, $prevAttacks->count());
        $this->assertEquals($attack1->class_name, $prevAttacks[0]->class_name);
    }

    public function testGetNews() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $this->assertEmpty(Attack::getNews());

        $attackNews = Attack::create('SynFlood', $red->id, $blue->id);
        $attackNews->isNews = true;
        Attack::updateAttack($attackNews);
        $attackNotNews = Attack::create('SQLInjection', $red->id, $blue->id);
        $attackNotNews->isNews = false;
        Attack::updateAttack($attackNotNews);

        $news = Attack::getNews();
        $this->assertEquals(1, $news->count());
        $this->assertEquals(true, $news[0]->isNews);
        $this->assertEquals($attackNews->class_name, $news[0]->class_name);
    }

    public function testGetUnreadDetectedAttacks() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $this->assertEmpty(Attack::getUnreadDetectedAttacks($blue->id));

        $attackNotNotified = Attack::create('SynFlood', $red->id, $blue->id);
        $attackNotNotified->detection_level = 1;
        $attackNotNotified->notified = false;
        Attack::updateAttack($attackNotNotified);
        $attackNotified = Attack::create('SQLInjection', $red->id, $blue->id);
        $attackNotified->detection_level = 1;
        $attackNotified->notified = true;
        Attack::updateAttack($attackNotified);

        $attacks = Attack::getUnreadDetectedAttacks($blue->id);
        $this->assertEquals(1, $attacks->count());
        $this->assertEquals('SynFlood', $attacks[0]->class_name);
    }

    public function testGetUnreadDetectedGetsCorrectBlueTeam() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $blue2 = Team::factory()->create();

        $correctAttack = Attack::create('SQLInjection', $red->id, $blue->id);
        $correctAttack->detection_level = 1;
        $correctAttack->notified = false;
        Attack::updateAttack($correctAttack);
        $wrongAttack = Attack::create('SynFlood', $red->id, $blue2->id);
        $wrongAttack->detection_level = 1;
        $wrongAttack->notified = false;

        $attacks = Attack::getUnreadDetectedAttacks($blue->id);
        $this->assertEquals(1, $attacks->count());
        $this->assertEquals($correctAttack->class_name, $attacks[0]->class_name);
    }

    public function testSetNotified() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $this->assertNull($attack->notified);
        $attack->setNotified(true);
        $this->assertTrue($attack->notified);
    }

    public function testSetNews() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $this->assertNull($attack->isNews);
        $attack->setNews(true);
        $this->assertTrue($attack->isNews);
    }

    public function testSetSuccess() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $this->assertNull($attack->success);
        $attack->setSuccess(true);
        $this->assertTrue($attack->success);
    }

    public function testCalculateDetectedTrue() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $attack->calculated_detection_risk = 5;
        Attack::updateAttack($attack);
        $attack->calculateDetected();
        $this->assertTrue($attack->detection_level >= 1);
    }

    public function testCalculateDetectedFalse() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $attack->calculated_detection_risk = 0;
        Attack::updateAttack($attack);
        $attack->calculateDetected();
        $this->assertEquals(0, $attack->detection_level);
    }

    public function testCalculateDetectedAfterSetSuccess() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $attack->success = true;
        $attack->calculated_detection_risk = 5;
        Attack::updateAttack($attack);
        $attack->calculateDetected();
        $this->assertNotEquals(0, $attack->detection_level);
    }

    public function testChangeDifficulty() {
        $baseAttack = new SQLInjectionAttack;
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);

        $this->assertEquals($baseAttack->difficulty, $attack->difficulty);
        $attack->changeDifficulty(10);
        $this->assertEquals(5, $attack->calculated_difficulty);
        $attack->changeDifficulty(-10);
        $this->assertEquals(1, $attack->calculated_difficulty);
        $attack->difficulty = 5;
        $attack->calculated_difficulty = 5;
        $attack->changeDifficulty(-.2);
        $this->assertEquals(4, $attack->calculated_difficulty);
    }

    public function testChangeDetectionRisk() {
        $baseAttack = new SQLInjectionAttack;
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $attack->changeDetectionRisk(10);
        $this->assertEquals(5, $attack->calculated_detection_risk);
        $attack->changeDetectionRisk(-10);
        $this->assertEquals(0, $attack->calculated_detection_risk);
        $attack->detection_risk = 5;
        $attack->calculated_detection_risk = 5;
        $attack->changeDetectionRisk(-.2);
        $this->assertEquals(4, $attack->calculated_detection_risk);
    }

    public function testCreateAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $this->assertEquals(SQLInjectionAttack::class, get_class($attack));
        $this->assertEquals($red->id, $attack->redteam);
        $this->assertEquals($blue->id, $attack->blueteam);
    }

    public function testCreateInvalidAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $this->expectException(AttackNotFoundException::class);
        $attack = Attack::create('NotAnAttack', $red->id, $blue->id);
    }

    
    public function testCreateAttackInvalidTeams() {
        $this->expectException(TeamNotFoundException::class);
        $attack = Attack::create('SQLInjection', 0, 1);
    }

    public function testUpdateAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SynFlood', $red->id, $blue->id);
        $attack->difficulty = 1;
        $att = Attack::updateAttack($attack);
        $this->assertEquals(1, $att->difficulty);
        $dbAttack = Attack::find(1);
        $this->assertEquals(1, $dbAttack->difficulty);
    }

    public function testInternalOnPreAttackNoToken(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SynFlood', $red->id, $blue->id);
        $attack->tags = ['Internal'];
        $attack = Attack::updateAttack($attack);
        $attack->onPreAttack();
        $this->assertFalse($attack->possible);
        $this->assertEquals(0, $attack->detection_level);
        $this->assertEquals("No access token.", $attack->errormsg);
    }

    public function testInternalOnPreAttackWithToken(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $asset = new AccessTokenAsset();
        $token = Inventory::factory()->create(['asset_name' => 'AccessToken', 'team_id' => $red->id, 'info' => $blue->name]);
        $attack = Attack::create('SynFlood', $red->id, $blue->id);
        $attack->tags = ['Internal'];
        $attack = Attack::updateAttack($attack);
        $attack->onPreAttack();
        $this->assertTrue($attack->possible);
    }

    public function testAnalystAddsAnalysisRiskAttacks(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Inventory::factory()->create(['asset_name' => 'SecurityAnalyst', 'team_id' => $blue->id]);
        $attack = Attack::create('SynFlood', $red->id, $blue->id);
        $attack->calculated_detection_risk = 5;
        $attack->analysis_risk = 2;
        $attack->calculated_analysis_risk = 2;
        $analysis_risk = $attack->calculated_analysis_risk;
        $attack->calculateDetected();
        $this->assertEquals($analysis_risk + (.5 * $analysis_risk), $attack->calculated_analysis_risk);
    }

    public function testGetName(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SynFlood', $red->id, $blue->id);
        $attack->detection_level = 1;
        Attack::updateAttack($attack);
        $this->assertNotEquals($attack->name, $attack->getName());
        $attack->detection_level = 2;
        Attack::updateAttack($attack);
        $this->assertEquals($attack->name, $attack->getName());
    }

    //Test AddToken tag

    public function testAddTokenTag1Token(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $asset = new AccessTokenAsset();
        $token = Inventory::factory()->create(['asset_name' => 'AccessToken', 'team_id' => $red->id, 'info' => $blue->name]);
        $seg = Inventory::factory()->create(['asset_name' => 'SegregateDuties', 'team_id' => $blue->id]);
        $attack = Attack::create('BackdoorBasic', $red->id, $blue->id);
        $attack->onPreAttack();
        $this->assertFalse($attack->possible);
    }

    public function testAddTokenTag2Token(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $asset = new AccessTokenAsset();
        $token = Inventory::factory()->create(['asset_name' => 'AccessToken', 'team_id' => $red->id, 'info' => $blue->name, 'quantity' => 2]);
        $seg = Inventory::factory()->create(['asset_name' => 'SegregateDuties', 'team_id' => $blue->id]);
        $attack = Attack::create('BackdoorBasic', $red->id, $blue->id);
        $attack->onPreAttack();
        $this->assertTrue($attack->possible);
    }

    public function testRedTeamGainsMoney(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $this->assertEquals(0, $red->balance);
        $attack->onAttackComplete();
        $red->refresh();
        $this->assertEquals($attack->energy_cost, $red->balance);
    }
}
