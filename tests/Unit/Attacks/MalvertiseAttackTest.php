<?php

namespace Tests\Attacks\Unit;

use Auth;
use Illuminate\Http\Request;
use App\Models\Assets\AdDeptAsset;
use App\Models\Attacks\MalvertiseAttack;
use App\Http\Controllers\AttackController;
use App\Models\Team;
use App\Models\User;
use App\Models\Attack;
use App\Models\Inventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MalvertiseAttackTest extends TestCase {
    use RefreshDatabase;

    public function createAttackAndTeams() {
        $user = User::factory()->create();
        $this->be($user);
        $red = Team::factory()->red()->create();
        Auth::user()->redteam = $red->id;
        Auth::user()->update();
        $blue = Team::factory()->create();
        $malvertiseAttack = new MalvertiseAttack();
        $attack = Attack::create($malvertiseAttack->class_name, $red->id, $blue->id);
        $attack = Attack::get($attack->class_name, $attack->redteam, $attack->blueteam);
        return $attack;
    }

    public function testMalvertiseNoAssets() {
        $attack = $this->createAttackAndTeams();
        $expectedAttack = $attack;
        $attack->onPreAttack();
        $this->assertEquals($expectedAttack, $attack);
        $this->assertEquals(false, $attack->possible);
    }

    public function testMalvertiseAndAdDept() {
        $attack = $this->createAttackAndTeams();
        $adDept = new AdDeptAsset;
        Inventory::factory()->create(['team_id' => $attack->blueteam, 'asset_name' => $adDept->class_name]);
        $attack->onPreAttack();
        $this->assertEquals(true, $attack->possible);
    }

    public function testSynFloodMinigameSuccess() {
        $attack = $this->createAttackAndTeams();
        $attack->onPreAttack();
        $adDept = new AdDeptAsset;
        Inventory::factory()->create(['team_id' => $attack->blueteam, 'asset_name' => $adDept->class_name]);
        $controller = new AttackController();
        $request = Request::create('POST','attack/malvertise',[
            'attID' => $attack->id,
            'result' => 1,
        ]);
        $response = $controller->malvertise($request);
        $attackAfter = Attack::find($attack->id);
        $this->assertEquals($attack->name, $attackAfter->name);
        $this->assertEquals($attack->difficulty, $attackAfter->difficulty);
        $this->assertEquals(1, $attackAfter->success);
    }

    public function testSynFloodMinigameInvalid() {
        $attack = $this->createAttackAndTeams();
        $attack->onPreAttack();
        $controller = new AttackController();
        $request = Request::create('POST','attack/malvertise',[
            'attID' => $attack->id,
            'result' => 0,
        ]);
        $response = $controller->malvertise($request);
        $attackAfter = Attack::find($attack->id);
        $this->assertEquals($attack->name, $attackAfter->name);
        $this->assertEquals($attack->difficulty, $attackAfter->difficulty);
        $this->assertEquals(0, $attackAfter->success);
    }
}
