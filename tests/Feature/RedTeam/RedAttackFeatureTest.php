<?php

namespace Tests\Feature;

use Tests\TestCase;
use Auth;
use App\Models\User;
use App\Models\Team;
use App\Models\Attack;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RedAttackFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $team = Team::factory()->red()->create();
        $user = User::factory()->create([
            'redteam' => $team->id,
        ]);
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
        $response->assertViewIs('redteam.startAttack');
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

    public function testUserCanViewPossibleAttacks() {
        $blueteam = Team::factory()->create();
        $response = $this->post('/redteam/chooseattack', [
            'result' => $blueteam->name,
        ]);
        $response->assertViewIs('redteam.chooseAttack' );
        $response->assertSee("Select a method of attack against ".$blueteam->name);
        $firstAttackName = Attack::getAll()[0]->name;
        $response->assertSee($firstAttackName);
    }

    public function testShouldErrorWhenNoAttackSelect() {
        $blueteam = Team::factory()->create();
        $response = $this->post('/redteam/performattack', [
            'blueteam' => $blueteam->name,
        ]);
        $response->assertViewIs('redteam.startAttack');
        $response->assertSee("No-Attack-Selected");
    }

    public function testUserCanSelectAttack() {
        $blueteam = Team::factory()->create();
        $response = $this->post('/redteam/performattack', [
            'blueteam' => $blueteam->name,
            'result' => "Syn Flood",
        ]);
        $response->assertSee("Select a rate at which to send SYN packets");
    }

    public function testViewPreviousAttacksEmpty() {
        $response = $this->get('redteam/attacks');
        $response->assertViewIs('redteam.attacks');
        $response->assertSee("You havent done any attacks yet!");
    }

    public function testViewPreviousAttacks() {
        $blueteam = Team::factory()->create();
        $attack1 = Attack::create('SynFlood', Auth::user()->redteam, $blueteam->id);
        $attack1->detection_level = 1;
        Attack::updateAttack($attack1);
        $attack2 = Attack::create('SynFlood', Auth::user()->redteam, $blueteam->id);
        $attack2->detection_level = 2;
        Attack::updateAttack($attack2);
        $response = $this->get('redteam/attacks');
        $response->assertViewIs('redteam.attacks');
        $response->assertSeeInOrder([$attack1->name, $attack1->success, "True", "False", "False", $attack1->created_at,
            $attack2->name, $attack2->success, "True", "True", "False", $attack2->created_at]);
    }
}
