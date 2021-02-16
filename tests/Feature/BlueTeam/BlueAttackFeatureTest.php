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

class BlueAttackFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $user = User::factory()->create();
        $this->be($user);
    }

    public function testBlueTeamCanViewAttackNotificationsLevel3()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack1->detection_level = 3;
        $attack1->setNotified(false);
        $attack2 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack2->detection_level = 3;
        $attack2->setNotified(false);
        $response = $this->actingAs($user)->get('/blueteam/home');
        $response->assertSeeInOrder([$attack1->name, $attack2->name]);
        $response->assertSee($red->name);
    }

    public function testBlueTeamCanViewAttackNotificationsLevel2()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack1->detection_level = 2;
        $attack1->setNotified(false);
        $attack2 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack2->detection_level = 2;
        $attack2->setNotified(false);
        $response = $this->actingAs($user)->get('/blueteam/home');
        $response->assertSeeInOrder([$attack1->name, $attack2->name]);
        $response->assertDontSee($red->name);
    }

    public function testBlueTeamAttackNotificationsLevel1()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(false);

        $response = $this->actingAs($user)->get('/blueteam/home');
        $response->assertSee("Your team was attacked while you were away!");
        $response->assertDontSee($attack1->name);
    }

    public function testBlueTeamCanClearAttackNotifications()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(false);

        $response = $this->actingAs($user)->get('/blueteam/clearNotifs');
        $response->assertViewIs('blueteam.home');
        $response->assertDontSee($attack1->name);
    }

    public function testBlueTeamCanBroadcastAttacks()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 3;
        $attack1->setNotified(false);
        $attack1 = Attack::find(1);

        $response = $this->actingAs($user)->post('/blueteam/broadcast', [
            'attID' => $attack1->id,
        ]);
        $response->assertViewIs('blueteam.home');
        $response->assertDontSee($attack1->class_name);

        $response = $this->actingAs($user)->get('/blueteam/news');
        $response->assertSeeInOrder([$blue->name, $red->name]); //Check for 'redname attacked bluename' text basically. Change when we add more to news page?
    }

    public function testBlueTeamCanSeeBroadcastButtonHomePage()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);

        $response = $this->get('blueteam/home');
        $response->assertDontSee('Broadcast');

        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(false);
        $attack1 = Attack::find(1);

        $response = $this->get('blueteam/home');
        $response->assertSee('Broadcast');
    }

    public function testBlueTeamCanSeeBroadcastButtonAttacksPage()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);

        $response = $this->get('blueteam/attacks');
        $response->assertDontSee('Broadcast');

        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(true);
        $attack1 = Attack::find(1);

        $response = $this->get('blueteam/attacks');
        $response->assertSee('Broadcast');
    }

    public function testBlueTeamCannotSeeBroadcastOldAttacks()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);

        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        $attack1->setNotified(true);
        $attack1 = Attack::find(1);
        $attack1->created_at = $attack1->created_at->subDays(4); //more than 3 days is 'old'
        $attack1->update();
        
        $response = $this->get('blueteam/attacks');
        $response->assertDontSee('Broadcast');
    }

    public function testTeamCanSeeAnalyzeButtonLvl1()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        Attack::updateAttack($attack1);

        $this->get('blueteam/attacks')->assertSee('Analyze');
    }

    public function testTeamCannotSeeAnalyzeButtonLvl2()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 2;
        Attack::updateAttack($attack1);

        $response = $this->get('blueteam/attacks');
        $response->assertSee($attack1->name);
        $response->assertDontSee('Analyze');
    }

    public function testTeamCanPayToAnalyze()
    {
        $blue = Team::factory()->create(['balance' => 1000,]);
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $expectedBalance = $blue->balance - 500; //500 to analyze
        $red = Team::factory()->red()->create();
        $this->be($user);
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        Attack::updateAttack($attack1);

        $response = $this->post('blueteam/analyzeAttack', ['attID' => $attack1->id,]);
        $response->assertViewIs('blueteam.attacks');
        $response->assertSee($attack1->name);
        $response->assertSee('Revenue: '.$expectedBalance);
    }

    public function testTeamCannotAnalyzeWithoutMoney()
    {
        $blue = Team::factory()->create(['balance' => 0,]);
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();
        $this->be($user);
        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        Attack::updateAttack($attack1);

        $response = $this->post('blueteam/analyzeAttack', ['attID' => $attack1->id,]);
        $response->assertViewIs('blueteam.attacks');
        $response->assertDontSee($attack1->name);
    }

    public function testAttacksPageDisplaysCorrectInfo()
    {
        $blue = Team::factory()->create();
        $user = User::factory()->create([
            'blueteam' => $blue->id,
            'leader' => 1,
        ]);
        $red = Team::factory()->red()->create();

        $attack1 = Attack::create('SynFlood', $red->id, $blue->id);
        $attack1->detection_level = 1;
        Attack::updateAttack($attack1);
        $attack2 = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack2->detection_level = 2;
        Attack::updateAttack($attack2);
        $response = $this->actingAs($user)->get('blueteam/attacks');
        $response->assertDontSee($attack1->name);
        $response->assertSee($attack2->name);
    }

}