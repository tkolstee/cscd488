<?php

namespace Tests\Unit;

use App\Models\Payload;
use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Attack;
use App\Models\Inventory;
use App\Models\Payloads\Xss;
use App\Models\Payloads\Dos;
use App\Models\Payloads\Destruction;
use App\Models\Payloads\BasicAccess;
use App\Exceptions\TeamNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayloadTest extends TestCase {
    use RefreshDatabase;

    public function testGetAllPayloads() {
        $payloads = Payload::getAll();
        $this->assertNotEquals(0, count($payloads));
        $this->assertTrue(is_subclass_of($payloads[0], 'App\Models\Payload'));
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
        $user = User::factory()->create();
        $blueteam = Team::factory()->create();
        $redteam = Team::factory()->red()->create();
        $user->redteam = $redteam->id;
        $user->update();
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

        $blueteam = $blueteam->fresh();
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
        $this->assertEquals('AccessToken', $redteam->assets()->first()->class_name);
    }
}
