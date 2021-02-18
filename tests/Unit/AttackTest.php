<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attack;
use App\Models\Team;
use App\Models\Inventory;
use App\Exceptions\AttackNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Models\Attacks\SQLInjectionAttack;
use App\Models\Assets\AccessTokenAsset;
use App\Models\Bonus;
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
        $attack->calculated_detection_chance = 1;
        Attack::updateAttack($attack);
        $attack->calculateDetected();
        $this->assertTrue($attack->detection_level >= 1);
    }

    public function testCalculateDetectedFalse() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $attack->calculated_detection_chance = 0;
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
        $attack->calculated_detection_chance = 1;
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

        $this->assertEquals($baseAttack->success_chance, $attack->success_chance);
        $attack->changeSuccessChance(10);
        $this->assertEquals(1, $attack->calculated_success_chance);
        $attack->changeSuccessChance(-10);
        $this->assertEquals(0, $attack->calculated_success_chance);
        $attack->success_chance = 1;
        $attack->calculated_success_chance = 1;
        $attack->changeSuccessChance(-.2);
        $this->assertEquals(.8, $attack->calculated_success_chance);
    }

    public function testChangeDetectionChance() {
        $baseAttack = new SQLInjectionAttack;
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack = Attack::find(1);
        $attack->changeDetectionChance(10);
        $this->assertEquals(1, $attack->calculated_detection_chance);
        $attack->changeDetectionChance(-10);
        $this->assertEquals(0, $attack->calculated_detection_chance);
        $attack->detection_chance = 1;
        $attack->calculated_detection_chance = 1;
        $attack->changeDetectionChance(-.2);
        $this->assertEquals(0.8, $attack->calculated_detection_chance);
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
        $attack->success_chance = 1;
        $att = Attack::updateAttack($attack);
        $this->assertEquals(1, $att->success_chance);
        $dbAttack = Attack::find(1);
        $this->assertEquals(1, $dbAttack->success_chance);
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

    public function testAnalystAddsAnalysisChanceAttacks(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        Inventory::factory()->create(['asset_name' => 'SecurityAnalyst', 'team_id' => $blue->id]);
        $attack = Attack::create('SynFlood', $red->id, $blue->id);
        $attack->calculated_detection_chance = 1;
        $attack->analysis_chance = 0.4;
        $attack->calculated_analysis_chance = 0.4;
        $analysis_risk = $attack->calculated_analysis_chance;
        $attack->onPreAttack();
        $this->assertEquals($analysis_risk + (.3 * $analysis_risk), $attack->calculated_analysis_chance);
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

    public function testRedTeamGainsMoneyOnSuccess(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $this->assertEquals(0, $red->balance);
        $attack->success = true;
        Attack::updateAttack($attack);
        $attack->onAttackComplete();
        $red->refresh();
        $this->assertEquals($attack->energy_cost, $red->balance);
    }

    public function testRedTeamDoesNotGainMoneyOnFailure(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $this->assertEquals(0, $red->balance);
        $attack->onAttackComplete();
        $red->refresh();
        $this->assertEquals(0, $red->balance);
    }

    public function testDisablePrereqs(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack1 = Attack::create('SQLInjection', $red->id, $blue->id);
        $this->assertFalse(\App\Models\Game::prereqsDisabled());
        $attack1->onPreAttack();
        $this->assertFalse($attack1->possible);
        $this->assertEquals("Unsatisfied prereqs for this attack", $attack1->errormsg);

        \App\Models\Game::toggleDisablePrereqs();
        $attack2 = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack2->onPreAttack();
        $this->assertTrue($attack2->possible);
        $this->assertNotEquals("Unsatisfied prereqs for this attack", $attack2->errormsg);
    }

    public function testUntilAnalyzedRewardCannotBeUndetected() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack->detection_chance = 0; //Shouldn't be detected
        $attack->success_chance = 1; //Attack will succeed
        Attack::updateAttack($attack);
        $attack->onPreAttack();
        
        $tags = ['UntilAnalyzed'];
        $bonus = Bonus::createBonus($red->id, $tags);
        $bonus->target_id = $blue->id;
        $bonus->attack_id = $attack->id;
        $bonus->update();

        $this->assertEquals(0, $attack->detection_level);
        $attack->onAttackComplete();
        $this->assertEquals(1, $attack->detection_level);
    }

    public function testGetDifficulty() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack->calculated_success_chance = 0.20;
        $this->assertEquals(4, $attack->getDifficulty());
        $attack->calculated_success_chance = 0;
        $this->assertEquals(5, $attack->getDifficulty());
        $attack->calculated_success_chance = 1;
        $this->assertEquals(1, $attack->getDifficulty());
    }

    public function testGetDifficultyRounding() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack->calculated_success_chance = 0.10;
        $this->assertEquals(5, $attack->getDifficulty());
        $attack->calculated_success_chance = 0.95;
        $this->assertEquals(1, $attack->getDifficulty()); 
    }

    //Targeted Prereq Handler tests
  
    public function testTargetedPrereqNoPrereq(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('ImplantedHWOffice', $red->id, $blue->id);
        $attack->onPreAttack();
        $this->assertFalse($attack->possible);
    }

    public function testTargetedPrereqWrongTeam(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $blue2 = Team::factory()->create();
        $inv = Inventory::create([
            'asset_name' => 'PhysicalAccess',
            'team_id' => $red->id,
            'quantity' => 1,
            'level' => 1,
            'info' => $blue2->name,
        ]);
        $attack = Attack::create('ImplantedHWOffice', $red->id, $blue->id);
        $attack->onPreAttack();
        $this->assertFalse($attack->possible);
    }

    public function testTargetedPrereqValid(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $inv = Inventory::create([
            'asset_name' => 'PhysicalAccess',
            'team_id' => $red->id,
            'quantity' => 1,
            'level' => 1,
            'info' => $blue->name,
        ]);
        $attack = Attack::create('ImplantedHWOffice', $red->id, $blue->id);
        $attack->onPreAttack();
        $this->assertTrue($attack->possible);
    }
}
