<?php

namespace Tests\Attacks\Unit;

use App\Models\Assets\FirewallAsset;
use App\Models\Attacks\SynFloodAttack;
use App\Models\Team;
use App\Models\Attack;
use App\Models\Inventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SynFloodAttackTest extends TestCase {
    use RefreshDatabase;

    public function createAttackAndTeams() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $sqlAttack = new SynFloodAttack;
        return Attack::create($sqlAttack->class_name, $red->id, $blue->id);
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
        $expected->difficulty += 2;
        $attack->onPreAttack();
        $this->assertEquals($expected, $attack);
    }
}
