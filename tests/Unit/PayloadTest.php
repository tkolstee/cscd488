<?php

namespace Tests\Unit;

use App\Models\Payload;
use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Attack;
use App\Models\Payloads\ActionsOnObjective;
use App\Models\Payloads\AdWare;
use App\Models\Payloads\Xss;
use App\Models\Payloads\Dos;
use App\Models\Payloads\Destruction;
use App\Models\Payloads\BasicAccess;
use App\Models\Payloads\Confusion;
use App\Models\Payloads\Eavesdropping;
use App\Models\Payloads\PrivAccess;
use App\Models\Payloads\Evasion;
use App\Models\Payloads\Fuzzing;
use App\Models\Payloads\InformationStealing;
use App\Models\Payloads\WebsiteDefacement;
use App\Models\Payloads\Keylogger;
use App\Models\Payloads\Ransomware;
use App\Models\Payloads\MaliciousInsider;
use App\Models\Payloads\StolenLaptop;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayloadTest extends TestCase {
    use RefreshDatabase;

    public function testGetAllPayloads() {
        $payloads = Payload::getAll();
        $this->assertNotEquals(0, count($payloads));
        $this->assertTrue(is_subclass_of($payloads[0], 'App\Models\Payload'));
    }

    public function testPayloadOnPreAttackCanIncreaseSuccess() {
        $attack = $this->createTeamsAndAttack();
        $initialDiff = $attack->calculated_success_chance;
        $this->assertEquals(0.6, $attack->calculated_success_chance);
        $payload = new Payload;
        $payload->percentIncreasedSuccess = 20;
        $payload->onPreAttack($attack);
        $attack->fresh();
        $this->assertEquals($initialDiff * 1.2, $attack->calculated_success_chance);
    }

    public function testGetPayloadByTag() {
        $tag = 'EndpointExecutable';
        $payloads = Payload::getByTag($tag);
        $this->assertNotEquals(0, count($payloads));
        foreach ($payloads as $payload){
            $this->assertTrue(in_array($tag, $payload->tags));
        }
    }
    
    private function createTeamsAndAttack(){
        $blueteam = Team::factory()->create();
        $redteam = Team::factory()->red()->create();
        $user = User::factory()->create(['redteam' => $redteam->id]);
        $this->be($user);
        $attack = Attack::create('SynFlood',$redteam->id, $blueteam->id);
        $attack->onPreAttack();
        return $attack;
    }

    public function testXssPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new Xss();
        $payload->onAttackComplete($attack);
        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertEquals(2, count($bonus->tags));
        $this->assertTrue(in_array("UntilAnalyzed", $bonus->tags));
        $this->assertTrue(in_array("RevenueSteal", $bonus->tags));
        $this->assertEquals(10, $bonus->percentRevStolen);
    }

    public function testDosPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new Dos();
        $payload->onAttackComplete($attack);
        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertEquals(3, count($bonus->tags));
        $this->assertTrue(in_array("OneTurnOnly", $bonus->tags));
        $this->assertTrue(in_array("RevenueDeduction", $bonus->tags));
        $this->assertTrue(in_array("DetectionDeduction", $bonus->tags));
        $this->assertEquals(50, $bonus->percentRevDeducted);
        $this->assertEquals(20, $bonus->percentDetDeducted);
    }
    
    public function testDestructionPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $blueteam->balance = 1000;
        $blueteam->update();
        $payload = new Destruction();
        $payload->onAttackComplete($attack);

        $blueteam->refresh();
        $this->assertEquals(900, $blueteam->balance); //lose 10% balance
        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array("RevenueDeduction", $bonus->tags));
        $this->assertEquals(20, $bonus->percentRevDeducted);
    }

    public function testBasicAccessPayload(){
        $attack = $this->createTeamsAndAttack();
        $payload = new BasicAccess();
        $payload->onAttackComplete($attack);

        $this->assertEmpty(Bonus::all());
        $redteam = Team::find($attack->redteam);
        $inv = $redteam->inventories()->first();
        $this->assertEquals('AccessToken', $inv->asset_name);
        $this->assertEquals(1, $inv->level);
    }

    public function testPrivAccessPayload(){
        $attack = $this->createTeamsAndAttack();
        $payload = new PrivAccess();
        $payload->onAttackComplete($attack);

        $this->assertEmpty(Bonus::all());
        $redteam = Team::find($attack->redteam);
        $inv = $redteam->inventories()->first();
        $this->assertEquals('AccessToken', $inv->asset_name);
        $this->assertEquals(2, $inv->level);
    }

    public function testEvasionPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new Evasion();
        $payload->onAttackComplete($attack);

        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array('DetectionDeduction', $bonus->tags));
        $this->assertEquals(30, $bonus->percentDetDeducted);
    }

    public function testWebsiteDefacementPayload() {
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $blueteam->reputation = 1000;
        $blueteam->update();
        $payload = new WebsiteDefacement();
        $payload->onAttackComplete($attack);

        $blueteam->refresh();
        $this->assertEquals(800, $blueteam->reputation); //Lose 20% reputation
        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array("RevenueDeduction", $bonus->tags));
        $this->assertEquals(20, $bonus->percentRevDeducted);
    }

    public function testConfusionPayload() {
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new Confusion;
        $payload->onAttackComplete($attack);

        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array("DetectionDeduction", $bonus->tags));
        $this->assertEquals(20, $bonus->percentDetDeducted);
        $this->assertTrue(in_array("AnalysisDeduction", $bonus->tags));
        $this->assertEquals(20, $bonus->percentAnalDeducted);
    }

    public function testKeyloggerPayload() {
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new Keylogger;
        $payload->onAttackComplete($attack);

        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array("UntilAnalyzed", $bonus->tags));
        $this->assertTrue(in_array("AddTokens", $bonus->tags));
    }

    public function testRansomwarePayload() {
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new Ransomware;
        $payload->onAttackComplete($attack);

        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array("RevenueDeduction", $bonus->tags));
        $this->assertTrue(in_array("ChanceToRemove", $bonus->tags));
        $this->assertTrue(in_array("PayToRemove", $bonus->tags));
        $this->assertEquals(50, $bonus->percentRevDeducted);
        $this->assertEquals(10, $bonus->removalChance);
        $this->assertEquals(2, $bonus->removalCostFactor);
    }

    public function testAdwarePayload() {
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new AdWare;
        $payload->onAttackComplete($attack);

        $this->assertEquals(.20, $payload->percentIncreasedSuccess);
        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array("RevenueGeneration", $bonus->tags));
        $this->assertTrue(in_array("UntilAnalyzed", $bonus->tags));
        $this->assertEquals(100, $bonus->revenueGenerated);
    }

    public function testInformationStealingPayload() {
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new InformationStealing;

        $payload->onAttackComplete($attack);

        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertEquals($blueteam->id, $bonus->target_id);
        $this->assertTrue(in_array("DifficultyDeduction", $bonus->tags));
        $this->assertTrue(in_array("DetectionDeduction", $bonus->tags));
        $this->assertEquals(20, $bonus->percentDiffDeducted);
        $this->assertEquals(20, $bonus->percentDetDeducted);
    }
    
    public function testMaliciousInsiderPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new MaliciousInsider;
        $payload->onAttackComplete($attack);
        $inventories = $redteam->inventories();
        $this->assertEquals(2, count($inventories));
        $account = $inventories->where('asset_name', '=', 'ValidAccount')->first();
        $access = $inventories->where('asset_name', '=', 'PhysicalAccess')->first();
        $this->assertEquals($blueteam->name, $account->info);
        $this->assertEquals($blueteam->name, $access->info);
    }

    public function testEavesdroppingPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new Eavesdropping;
        $payload->onAttackComplete($attack);
        $bonuses = $redteam->getBonuses();
        $this->assertLessThanOrEqual(2, count($bonuses));
        foreach($bonuses as $bonus) {
            $this->assertEquals($redteam->id, $bonus->team_id);
            $this->assertEquals($blueteam->id, $bonus->target_id);
            $this->assertTrue(in_array('DetectionDeduction', $bonus->tags) || in_array('OneTurnOnly', $bonus->tags));
        }
        $inv = $redteam->inventories();
        $this->assertLessThanOrEqual(2, count($inv));
        foreach($inv as $asset){
            $this->assertEquals('AccessToken', $asset->asset_name);
        }
    }

    /*
    public function testStolenLaptopPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        $payload = new StolenLaptop;
        $payload->onAttackComplete($attack);
        $bonuses = $redteam->getBonuses();
        $this->assertLessThanOrEqual(2, count($bonuses));
        foreach($bonuses as $bonus) {
            $this->assertEquals($redteam->id, $bonus->team_id);
            $this->assertEquals($blueteam->id, $bonus->target_id);
            $this->assertTrue(in_array('DetectionDeduction', $bonus->tags) || in_array('OneTurnOnly', $bonus->tags));
        }
        $inv = $redteam->inventories();
        $this->assertLessThanOrEqual(3, count($inv));
        foreach($inv as $asset){
            $this->assertEquals('AccessToken', $asset->asset_name);
        }
    }*/

    public function testFuzzingPayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $payload = new Fuzzing;
        $payload->onAttackComplete($attack);
        $bonus = $redteam->getBonuses()->first();

        if ($bonus != null){
            $this->assertTrue(in_array("DetectionDeduction", $bonus->tags));
            $this->assertEquals(20, $bonus->percentDetDeducted);
            $this->assertTrue(in_array("AnalysisDeduction", $bonus->tags));
            $this->assertEquals(20, $bonus->percentAnalDeducted);
        }
        else {
            $inv = $redteam->inventories()->first();
            $this->assertEquals('AccessToken', $inv->asset_name);
            $this->assertEquals(2, $inv->level);
        }
    }

    public function testActionsOnObjectivePayload(){
        $attack = $this->createTeamsAndAttack();
        $redteam = Team::find($attack->redteam);
        $payload = new ActionsOnObjective;
        $payload->onAttackComplete($attack);
        $bonus = $redteam->getBonuses()->first();
        $this->assertEquals($redteam->id, $bonus->team_id);
        $this->assertTrue(in_array("RevenueSteal", $bonus->tags));
        $this->assertEquals(10, $bonus->percentRevStolen);
    }
}
