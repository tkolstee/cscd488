<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Attack;
use App\Models\Inventory;
use App\Models\Payload;
use App\Models\Payloads\Xss;
use App\Models\Payloads\Dos;
use App\Exceptions\TeamNotFoundException;

use Illuminate\Foundation\Testing\RefreshDatabase;

class PayloadTest extends TestCase {
    use RefreshDatabase;
    
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
        $this->assertEquals(0.5, $bonus->percentRevDeducted);
        $this->assertEquals(0.2, $bonus->percentDetDeducted);
    }
    
}