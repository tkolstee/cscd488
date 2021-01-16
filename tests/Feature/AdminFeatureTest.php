<?php

namespace Tests\Feature;

use Tests\TestCase;
use Auth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminFeatureTest extends TestCase 
{
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
    }

    public function testAdminCanVisitAdminPages() {
        $admin = User::factory()->admin()->create();
        $this->be($admin);
        $response = $this->get('/admin/home');
        $response->assertStatus(200);
        $response = $this->get('/admin/playerRegistration');
        $response->assertStatus(200);
        $response = $this->get('/home/chooseteam');
        $response->assertSee('Admin Home');
    }

    public function testNonAdminCannotVisitAdminPages() {
        $nonAdmin = User::factory()->create(['is_admin' => 0]);
        $this->be($nonAdmin);
        $response = $this->get('/admin/home');
        $response->assertStatus(404);
        $response = $this->get('/admin/playerRegistration');
        $response->assertStatus(404);
        $response = $this->get('/home/chooseteam');
        $response->assertDontSee('Admin Home');
    }

    public function testAdminCanSignUpUsers() {
        $admin = User::factory()->admin()->create();
        $this->be($admin);
        $user = User::factory()->make(['password' => bcrypt($password = 'password')]);
        $response = $this->post('/admin/playerRegistration', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
        $response->assertViewIs('admin.playerRegistration');
        //$response->assertSee("User created successfully!");

        Auth::logout();
        $this->post('/login', [
            'username' => $user->username,
            'password' => $password
        ]);
        $this->assertTrue(Auth::check());
    }
}
