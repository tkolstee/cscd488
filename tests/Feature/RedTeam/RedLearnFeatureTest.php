<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attack;
use Tests\TestCase;
use Auth;

class RedLearnFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        $user = User::factory()->create();
        $this->be($user);
    }

    public function testLearnPageDisplaysAttacks(){
        $response = $this->post('/redteam/learn');
        $response->assertViewIs('redteam.learn');
        $attacks = Attack::getLearnableAttacks();
        $response->assertSee($attacks[0]->name);
    }

    public function testLearnAttackDisplaysAttackLearnPage(){
        $response = $this->post('/learn/dos');
        $response->assertViewIs('redteam.learn.dos');
    }

}