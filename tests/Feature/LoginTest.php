<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseMigrations;
    protected $user;

    public function testUserCanViewLoginPage()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function testAuthenticatedUserCannotViewLoginPage()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/home');
    }

    public function testUserCanLoginWithValidCredentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'testPass'),
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => $password,
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'testPass'),
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'wrongPass',
        ]);
        $this->assertGuest();
    }

    public function testUserCanLogout()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'testPass'),
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => $password,
        ]);
        $response = $this->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function testUnauthUserCannotViewGamePages()
    {
        $response = $this->get('/home');
        $response->assertRedirect('/login');
        $response = $this->get('/blueteam/home');
        $response->assertRedirect('/login');
        $response = $this->get('/redteam/home');
        $response->assertRedirect('/login');
    }
}
