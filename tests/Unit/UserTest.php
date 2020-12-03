<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Exceptions\TeamNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase {
    use RefreshDatabase;

    public function testGetBlueTeam() {
        $team = Team::factory()->create();
        $user = User::factory()->create(['blueteam' => $team->id]);
        $this->assertEquals($team->name, $user->getBlueTeam()->name);
    }

    public function testGetNoBlueTeam() {
        $this->expectException(TeamNotFoundException::class);
        User::factory()->create()->getBlueTeam();
    }

    public function testGetRedTeam() {
        $team = Team::factory()->red()->create();
        $user = User::factory()->create(['redteam' => $team->id]);
        $this->assertEquals($team->name, $user->getRedTeam()->name);
    }

    public function testGetNoRedTeam() {
        $this->expectException(TeamNotFoundException::class);
        User::factory()->create()->getRedTeam();
    }

    public function testLeaveBlueTeamNoMembers() {
        $team = Team::factory()->create();
        $user = User::factory()->create(['blueteam' => $team->id, 'leader' => 1]);
        $user->leaveBlueTeam();
        $this->assertNull($user->blueteam);
    }

    public function testLeaveBlueTeamWithMembers() {
        $team = Team::factory()->create();
        $user = User::factory()->create(['blueteam' => $team->id, 'leader' => 1]);
        $member = User::factory()->create(['blueteam' => $team->id, 'leader' => 0]);
        $user->leaveBlueTeam();
        $this->assertNull($user->blueteam);
        $this->assertEquals($team->id, $member->blueteam);
        $this->assertNotNull($team->leader());
    }

    public function testLeaveRedTeam() {
        $team = Team::factory()->red()->create();
        $user = User::factory()->create(['redteam' => $team->id]);
        $user->leaveRedTeam();
        $this->assertNull($user->redteam);
    }

    public function testJoinBlueTeam() {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $this->assertTrue($user->joinBlueTeam($team->name));
        $this->assertEquals($team->id, $user->blueteam);
        $this->assertEquals(0, $user->leader);
    }

    public function testCreateBlueTeam() {
        $user = User::factory()->create();
        $this->assertTrue($user->createBlueTeam('name'));
        $this->assertNotNull($user->blueteam);
        $this->assertEquals(1, $user->leader);
    }

    public function testCreateRedTeam() {
        $user = User::factory()->create();
        $this->assertTrue($user->createRedTeam('name'));
        $this->assertNotNull($user->redteam);
    }
}
