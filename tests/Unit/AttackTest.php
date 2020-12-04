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

    public function testConvertToBaseAttack() {
        $derived = new SQLInjectionAttack;
        $base = Attack::convertToBase($derived);
        $this->assertEquals(Attack::class, get_class($base));
        $this->assertEquals($derived->name, $base->name);
        $this->assertEquals($derived->difficulty, $base->difficulty);
        $this->assertEquals($derived->tags, $base->tags);
        $this->assertEquals($derived->prereqs, $base->prereqs);
    }

    public function testConvertToDerivedAttack() {
        $base = new Attack;
        $base->class_name = "SQLInjection";
        $derived = Attack::convertToDerived($base);
        $this->assertEquals(SQLInjectionAttack::class, get_class($derived));
        $this->assertEquals($base->name, $derived->name);
        $this->assertEquals($base->difficulty, $derived->difficulty);
        $this->assertEquals($base->tags, $derived->tags);
        $this->assertEquals($base->prereqs, $derived->prereqs);
    }

    public function testConvertInvalidAttackToDerived() {
        $base = new Attack;
        $base->class_name = "NotAnAttack";
        $this->expectException(AttackNotFoundException::class);
        Attack::convertToDerived($base);
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
}
