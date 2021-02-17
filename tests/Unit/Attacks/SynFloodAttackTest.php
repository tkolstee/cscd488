<?php

namespace Tests\Attacks\Unit;

use Auth;
use Illuminate\Http\Request;
use App\Models\Assets\FirewallAsset;
use App\Models\Attacks\SynFloodAttack;
use App\Http\Controllers\AttackController;
use App\Models\Team;
use App\Models\User;
use App\Models\Attack;
use App\Models\Inventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SynFloodAttackTest extends TestCase {
    use RefreshDatabase;

    public function createAttackAndTeams() {
        $user = User::factory()->create();
        $this->be($user);
        $red = Team::factory()->red()->create();
        Auth::user()->redteam = $red->id;
        Auth::user()->update();
        $blue = Team::factory()->create();
        $synFloodAttack = new SynFloodAttack;
        $attack = Attack::create($synFloodAttack->class_name, $red->id, $blue->id);
        $attack = Attack::get($attack->class_name, $attack->redteam, $attack->blueteam);
        return $attack;
    }

    public function testSynFloodNoAssets() {
        $attack = $this->createAttackAndTeams();
        $expectedAttack = $attack;
        $attack->onPreAttack();
        $this->assertEquals($expectedAttack, $attack);
    }

    public function testSynFloodAndFirewall() {
        $attack = $this->createAttackAndTeams();
        $firewall = new FirewallAsset;
        Inventory::factory()->create(['team_id' => $attack->blueteam, 'asset_name' => $firewall->class_name]);
        $expected = $attack;
        $expected->success_chance += 2;
        $attack->onPreAttack();
        $this->assertEquals($expected, $attack);
    }

    public function testSynFloodMinigameSuccess() {
        $attack = $this->createAttackAndTeams();
        $attack->onPreAttack();
        $controller = new AttackController();
        $request = Request::create('POST','attack/synflood',[
            'attID' => $attack->id,
            'result1' => 1,
            'result2' => 1,
        ]);
        $response = $controller->synFlood($request);
        $attackAfter = Attack::find($attack->id);
        $this->assertEquals($attack->name, $attackAfter->name);
        $this->assertEquals($attack->success_chance, $attackAfter->success_chance);
        $this->assertEquals(1, $attackAfter->success);
    }

    public function testSynFloodMinigameResult1Is0() {
        $attack = $this->createAttackAndTeams();
        $attack->onPreAttack();
        $controller = new AttackController();
        $request = Request::create('POST','attack/synflood',[
            'attID' => $attack->id,
            'result1' => 0,
            'result2' => 1,
        ]);
        $response = $controller->synFlood($request);
        $attackAfter = Attack::find($attack->id);
        $this->assertEquals($attack->name, $attackAfter->name);
        $this->assertEquals($attack->success_chance, $attackAfter->success_chance);
        $this->assertEquals(0, $attackAfter->success);
    }

    public function testSynFloodMinigameResult2Is0() {
        $attack = $this->createAttackAndTeams();
        $attack->onPreAttack();
        $controller = new AttackController();
        $request = Request::create('POST','attack/synflood',[
            'attID' => $attack->id,
            'result1' => 1,
            'result2' => 0,
        ]);
        $response = $controller->synFlood($request);
        $attackAfter = Attack::find($attack->id);
        $this->assertEquals($attack->name, $attackAfter->name);
        $this->assertEquals($attack->success_chance, $attackAfter->success_chance);
        $this->assertEquals(0, $attackAfter->success);
    }

    public function testSynFloodMinigameBothResults0() {
        $attack = $this->createAttackAndTeams();
        $attack->onPreAttack();
        $controller = new AttackController();
        $request = Request::create('POST','attack/synflood',[
            'attID' => $attack->id,
            'result1' => 0,
            'result2' => 0,
        ]);
        $response = $controller->synFlood($request);
        $attackAfter = Attack::find($attack->id);
        $this->assertEquals($attack->name, $attackAfter->name);
        $this->assertEquals($attack->success_chance, $attackAfter->success_chance);
        $this->assertEquals(0, $attackAfter->success);
    }

}
