<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Game;
use Tests\TestCase;
use Auth;

class BlueMiscFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $team = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $this->be($user);
    }

    public function testUserCanViewBlueTeamPages()
    {
        $response = $this->get('/blueteam/home');
        $response->assertStatus(200);
        $response = $this->get('/blueteam/status');
        $response->assertStatus(200);
        $response = $this->get('/blueteam/store');
        $response->assertStatus(200);
    }

    public function testLeaderboardDisplaysInfo()
    {
        $blue1 = Team::factory()->create(['reputation' => 10000]);
        $blue2 = Team::factory()->create(['reputation' => 500]);
        $blue3 = Team::factory()->create(['reputation' => 100]);
        $user = User::factory()->create([
            'blueteam' => $blue1->id,
            'leader' => 1,
        ]);
        $this->be($user);
        $response = $this->get('blueteam/leaderboard');
        $response->assertSeeInOrder([$blue1->name, $blue1->reputation, 
                                    $blue2->name, $blue2->reputation,
                                    $blue3->name, $blue3->reputation]);
    }

    public function testReputationGain()
    {
        $team = Auth::user()->getBlueTeam();
        $game = Game::find(1);
        $this->get('blueteam/home')->assertSee("Reputation: 0");
        
        $team->created_at = $team->created_at->subDays(1);
        $team->update();
        $game->endTurn();
        $this->get('blueteam/home')->assertSee("Reputation: 50");//+50

        $team->created_at = $team->created_at->subDays(1);
        $team->update();
        $game->endTurn();
        $this->get('blueteam/home')->assertSee("Reputation: 150");//+100
    }

    public function testEndTurn(){
        $team = Auth::user()->getBlueTeam();
        $response = $this->post('/blueteam/endturn');
        $response->assertViewIs('blueteam.home');
        $response->assertSeeInOrder(["Wait until", 'next turn']);
    }

}
