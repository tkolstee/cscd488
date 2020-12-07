<?php

namespace Tests\Feature;

use Tests\TestCase;
use Auth;
use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttackFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        $redteam = Team::factory()->red()->create();
        $user = User::factory()->create(['redteam' => $redteam->id]);
        $this->be($user);
    }

    public function testUserWithNoRedTeamCannotAttack() {
        $newUser = User::factory()->create();
        $this->be($newUser);
        $response = $this->get('/redteam/home');
        $response->assertDontSee("Start Attack");
        $response = $this->get('/redteam/startattack');
        $response->assertViewIs('redteam.home');
    }

    public function testUserCanStartAttack() {
        $response = $this->get('/redteam/home');
        $response->assertSee("Start Attack");
        $response = $this->get('/redteam/startattack');
        $response->assertStatus(200)->assertViewIs('redteam.startAttack');
        $response->assertSee("Select a blue team to attack:");
    }

    public function testUserCanViewTeamsToAttack() {
        $blueteam1 = Team::factory()->create();
        $blueteam2 = Team::factory()->create();
        $blueteam3 = Team::factory()->create();
        $response = $this->get('/redteam/startattack');
        $response->assertSee([$blueteam1->name, $blueteam2->name, $blueteam3->name]);
    }

    public function testUserCannotAttackOwnTeam(){
        $blueteam = Team::factory()->create();
        Auth::user()->blueteam = $blueteam->id;
        Auth::user()->update();
        $response = $this->get('/redteam/startattack');
        $response->assertSee("Select a blue team to attack:");
        $response->assertDontSee($blueteam->name);
    }

    public function testShouldErrorWhenNoTeamSelected() {
        $response = $this->get('/redteam/chooseattack');
        $response->assertViewIs('redteam.startAttack');
        $response->assertSee("No-Team-Selected");
    }

    public function testSelectTeamChooseAttack() {
        $blueteam = Team::factory()->create();
        $response = $this->post('/redteam/chooseattack', [
            'result' => $blueteam->name,
        ]);
        $response->assertViewIs('redteam.chooseAttack' );
        $response->assertSee("Select a method of attack against ".$blueteam->name);
        $response->assertSee("SQL Injection");
        $response->assertSee("Syn Flood");
    }

    public function testUserCanViewPossibleAttacks() {
        $this->assertTrue(false);
    }

    public function testShouldErrorWhenNoAttackSelect() {
        $this->assertTrue(false);
    }

    public function testUserCanSelectAttack() {
        $this->assertTrue(false);
    }
}
