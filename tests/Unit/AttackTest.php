<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attack;
use App\Models\Team;
use App\Exceptions\AttackNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Models\Attacks\SQLInjectionAttack;
use App\Models\Attacks\SynFloodAttack;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttackTest extends TestCase {
    use RefreshDatabase;

    public function testGetAllAttacks() {
        $attacks = Attack::getAll();
        $expectedAttacks = [new SQLInjectionAttack,
                        new SynFloodAttack];
        $this->assertEquals($expectedAttacks, $attacks);
    }

    public function testGetAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = new Attack;
        $attack->class_name = 'SQLInjection';
        $attack->redteam = $red->id;
        $attack->blueteam = $blue->id;
        $attack->save();
        $retrievedAttack = Attack::get('SQLInjection', $red->id, $blue->id);
        $this->assertEquals(SQLInjectionAttack::class, get_class($retrievedAttack));
    }

    public function testGetInvalidAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = new Attack;
        $attack->class_name = 'NotAnAttack';
        $attack->redteam = $red->id;
        $attack->blueteam = $blue->id;
        $attack->save();
        $this->expectException(AttackNotFoundException::class);
        Attack::get('NotAnAttack', $red->id, $blue->id);
    }

    public function testGetPreviousAttacks() {
        $red = Team::factory()->red()->create();
        $diffRed = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $this->assertEmpty(Attack::getPreviousAttacks($red));

        Attack::create('SynFlood', $red->id, $blue->id);
        $attack1 = Attack::get('SynFlood', $red->id, $blue->id);
        Attack::create('SQLInjection', $red->id, $blue->id);
        $attack2 = Attack::get('SQLInjection', $red->id, $blue->id);
        Attack::create('SynFlood', $diffRed->id, $blue->id);

        $prevAttacks = Attack::getPreviousAttacks($red->id);
        $this->assertEquals(2, $prevAttacks->count());
        $prevAttack1 = $prevAttacks[0];
        $this->assertEquals($attack1->class_name, $prevAttack1->class_name);
        $this->assertEquals($red->id, $prevAttack1->redteam);
        $prevAttack2 = $prevAttacks[1];
        $this->assertEquals($attack2->class_name, $prevAttack2->class_name);
        $this->assertEquals($red->id, $prevAttack2->redteam);
    }

    public function testCreateAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $this->assertEquals(SQLInjectionAttack::class, get_class($attack));
        $this->assertEquals($red->id, $attack->redteam);
        $this->assertEquals($blue->id, $attack->blueteam);
    }

    public function testCreateInvalidAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $this->expectException(AttackNotFoundException::class);
        $attack = Attack::create('NotAnAttack', $red->id, $blue->id);
    }

    
    public function testCreateAttackInvalidTeams() {
        $this->expectException(TeamNotFoundException::class);
        $attack = Attack::create('SQLInjection', 0, 1);
    }

    public function testUpdateAttack() {
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $attack = Attack::create('SynFlood', $red->id, $blue->id);
        $attack->difficulty = 1;
        $att = Attack::updateAttack($attack);
        $this->assertEquals(1, $att->difficulty);
        $dbAttack = Attack::find(1);
        $this->assertEquals(1, $dbAttack->difficulty);
    }
}
