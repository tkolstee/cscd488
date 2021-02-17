<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Attack;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SQLMinigameFeatureTest extends TestCase {
    use RefreshDatabase;

    public function createAttack(){
        $red = Team::factory()->red()->create();
        $blue = Team::factory()->create();
        $user = User::factory()->create(['redteam' => $red->id]);
        $this->be($user);
        $attack = Attack::create('SQLInjection', $red->id, $blue->id);
        $attack->onPreAttack();
        return $attack;
    }

    public function testSqlNonInjectionInput(){
        $attack = $this->createAttack();
        $attack->calculated_difficulty = 2;
        Attack::updateAttack($attack);
        $response = $this->post('/attack/sqlinjection', [
            'attID' => $attack->id,
            'url' => "username",
        ]);
        $response->assertViewIs('minigame.sqlinjection');
        $response->assertSee("Nothing happened!");
    }

    public function testSqlInjectionLevel1Display(){
        $attack = $this->createAttack();
        $attack->calculated_difficulty = 1;
        Attack::updateAttack($attack);
        $response = $this->post('/redteam/savePayload', [
            'attID' => $attack->id,
            'result' => 'WebsiteDefacement'
        ]);
        $response->assertViewIs('minigame.sqlinjection');
        $response->assertSee("Attempt to cause a SQL error!");
        $response->assertDontSee("Enter admin's password:");
    }

    public function testSqlInjectionLevel1(){
        $attack = $this->createAttack();
        $attack->calculated_difficulty = 1;
        Attack::updateAttack($attack);
        $response = $this->post('/attack/sqlinjection', [
            'attID' => $attack->id,
            'url' => "'",
        ]);
        $response->assertViewIs('redteam.home');
        $response->assertSee("You caused a query error!");
    }

    public function testSqlInjectionLevel2Display(){
        $attack = $this->createAttack();
        $attack->calculated_difficulty = 2;
        Attack::updateAttack($attack);
        $response = $this->post('/redteam/savePayload', [
            'attID' => $attack->id,
            'result' => 'WebsiteDefacement'
        ]);
        $response->assertViewIs('minigame.sqlinjection');
        $response->assertSee("Attempt to find the admins password using sql injection!");
        $response->assertSee("Enter admins password:");
    }

    public function testSqlInjectionLevel2(){
        $attack = $this->createAttack();
        $attack->calculated_difficulty = 2;
        Attack::updateAttack($attack);
        
        $response = $this->post('/attack/sqlinjection', [
            'attID' => $attack->id,
            'url' => "1' OR 1=1--",
        ]);
        $adminPass = DB::connection('sql_minigame')->table('users')->where('username', 'admin')->first()->password;
        $response->assertViewIs('minigame.sqlinjection');
        $response->assertSee($adminPass);

        $response = $this->post('/attack/sqlinjectioncheck', [
            'attID' => $attack->id,
            'pass' => $adminPass,
        ]);
        $response->assertViewIs('redteam.home');
        $response->assertSee("You successfully discovered the admin's password!");
    }
}
