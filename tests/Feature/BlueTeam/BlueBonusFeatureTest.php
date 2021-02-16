<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Attack;
use App\Models\Asset;
use App\Models\Bonus;
use App\Models\Game;
use Tests\TestCase;
use App\Models\Inventory;
use Auth;

class BlueBonusFeatureTest extends TestCase
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

    public function testStatusDisplaysBonuses(){
        $blue = Auth::user()->getBlueTeam();
        $red = Team::factory()->red()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack->detection_level = 1;
        Attack::updateAttack($attack);
        $bonus = Bonus::createBonus($red->id, []);
        $bonus->target_id = $blue->id;
        $bonus->attack_id = $attack->id;
        $bonus->payload_name = "SQLPayload";
        $bonus->update();
        $attack2 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack2->detection_level = 2;
        Attack::updateAttack($attack2);
        $bonus2 = Bonus::createBonus($red->id, []);
        $bonus2->target_id = $blue->id;
        $bonus2->attack_id = $attack2->id;
        $bonus2->payload_name = "SynFloodPayload";
        $bonus2->update();
        $attack3 = Attack::create('Dos', $red->id, $blue->id);
        $attack3->detection_level = 3;
        Attack::updateAttack($attack3);
        $bonus3 = Bonus::createBonus($red->id, []);
        $bonus3->target_id = $blue->id;
        $bonus3->attack_id = $attack3->id;
        $bonus3->payload_name = "DosPayload";
        $bonus3->update();
        $response = $this->get('blueteam/status');
        $response->assertViewIs('blueteam.status');
        $response->assertSeeInOrder([
            "?", "?", 
            $bonus2->payload_name, "?",
            $bonus3->payload_name, $red->name
        ]);
        $response->assertDontSee([
            $bonus->payload_name
        ]);
    }

    public function testPayToRemoveBonus() {
        $blue = Auth::user()->getBlueTeam();
        $red = Team::factory()->red()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $tags = ["PayToRemove"];
        $bonus = Bonus::createBonus($red->id, $tags);
        $bonus->target_id = $blue->id;
        $bonus->attack_id = $attack->id;
        $bonus->removalCostFactor = 2;
        $bonus->payload_name = "PayloadName";
        $bonus->update();

        $this->get('blueteam/status')->assertSee("Pay To Remove");
        $response = $this->post('/blueteam/removeBonus', [
            'bonusID' => $bonus->id,
        ]);
        $response->assertViewIs('blueteam.status')->assertDontSee("Pay To Remove");
    }
}
