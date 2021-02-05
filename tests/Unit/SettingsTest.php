<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class SettingsTest extends TestCase {
    use RefreshDatabase;

    private function createUserAndTeams(){
        $user = User::factory()->create();
        $blueteam = Team::factory()->create();
        $redteam = Team::factory()->red()->create();
        $user->blueteam = $blueteam->id;
        $user->redteam = $redteam->id;
        $user->update();
        $this->be($user);
        return $user;
    }

    public function testSettings(){
        $user = $this->createUserAndTeams();
        $controller = new UserController();
        $request = Request::create('/settings', 'POST', []);
        $response = $controller->settings($request);
        $this->assertEquals($user->blueteam, $response->blueteam->id);
        $this->assertEquals($user->redteam, $response->redteam->id);
    }

    public function testChangeNameEmpty(){
        $user = $this->createUserAndTeams();
        $userNameBefore = $user->name;
        $controller = new UserController();
        $request = Request::create('/changename', 'POST', [
            'name' => null,
        ]);
        $response = $controller->changename($request);
        $user->fresh();
        $this->assertEquals($userNameBefore, $user->name);
    }

    public function testChangeName(){
        $user = $this->createUserAndTeams();
        $controller = new UserController();
        $request = Request::create('/changename', 'POST', [
            'name' => 'newName',
        ]);
        $response = $controller->changeName($request);
        $user->fresh();
        $this->assertEquals("newName", $user->name);
    }

    public function testChangeUserNameNull(){
        $user = $this->createUserAndTeams();
        $userNameBefore = $user->username;
        $controller = new UserController();
        $request = Request::create('/changeusername', 'POST', [
            'username' => null,
        ]);
        $response = $controller->changeUserName($request);
        $user->fresh();
        $this->assertEquals($userNameBefore, $user->username);
    }

    public function testChangeUserNameTaken(){
        $user = $this->createUserAndTeams();
        $userNameBefore = $user->username;
        $user2 = User::factory()->create();
        $controller = new UserController();
        $request = Request::create('/changeusername', 'POST', [
            'username' => $user2->username,
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->changeUserName($request);
    }

    public function testChangeUserName(){
        $user = $this->createUserAndTeams();
        $controller = new UserController();
        $request = Request::create('/changeusername', 'POST', [
            'username' => 'newUserName',
        ]);
        $response = $controller->changeUserName($request);
        $user->fresh();
        $this->assertEquals("newUserName", $user->username);
    }

    public function testChangeEmailNull(){
        $user = $this->createUserAndTeams();
        $emailBefore = $user->email;
        $controller = new UserController();
        $request = Request::create('/changeemail', 'POST', [
            'email' => null,
        ]);
        $response = $controller->changeEmail($request);
        $user->fresh();
        $this->assertEquals($emailBefore, $user->email);
    }

    public function testChangeEmailInvalid(){
        $user = $this->createUserAndTeams();
        $controller = new UserController();
        $request = Request::create('/changeemail', 'POST', [
            'email' => "email.com",
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->changeEmail($request);
    }

    public function testChangeEmail(){
        $user = $this->createUserAndTeams();
        $controller = new UserController();
        $request = Request::create('/changeemail', 'POST', [
            'email' => "email@test.com",
        ]);
        $response = $controller->changeEmail($request);
        $user->fresh();
        $this->assertEquals("email@test.com", $user->email);
    }

    public function testChangePasswordInvalidOld(){
        $user = $this->createUserAndTeams();
        $user->password = Hash::make("password");
        $user->update();
        $controller = new UserController();
        $request = Request::create('/changepassword', 'POST', [
            'oldPassword' => "wrongpassword",
            'newPassword' => "newPassword",
            'newPasswordConfirm' => "newPasswordConfirm",
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->changePassword($request);
    }

    public function testChangePasswordNullOld(){
        $user = $this->createUserAndTeams();
        $passwordBefore = Hash::make("password");
        $user->password = $passwordBefore;
        $user->update();
        $controller = new UserController();
        $request = Request::create('/changepassword', 'POST', [
            'oldPassword' => null,
            'newPassword' => "newPassword",
            'newPasswordConfirm' => "newPasswordConfirm",
        ]);
        $response = $controller->changePassword($request);
        $user->fresh();
        $this->assertEquals($passwordBefore, $user->password);
    }

    public function testChangePasswordNullNew(){
        $user = $this->createUserAndTeams();
        $passwordBefore = Hash::make("password");
        $user->password = $passwordBefore;
        $user->update();
        $controller = new UserController();
        $request = Request::create('/changepassword', 'POST', [
            'oldPassword' => "password",
            'newPassword' => null,
            'newPasswordConfirm' => "newPasswordConfirm",
        ]);
        $response = $controller->changePassword($request);
        $user->fresh();
        $this->assertEquals($passwordBefore, $user->password);
    }

    public function testChangePasswordNewShort(){
        $user = $this->createUserAndTeams();
        $passwordBefore = Hash::make("password");
        $user->password = $passwordBefore;
        $user->update();
        $controller = new UserController();
        $request = Request::create('/changepassword', 'POST', [
            'oldPassword' => "password",
            'newPassword' => "short",
            'newPasswordConfirm' => "newPasswordConfirm",
        ]);
        $response = $controller->changePassword($request);
        $user->fresh();
        $this->assertEquals($passwordBefore, $user->password);
    }

    public function testChangePasswordConfirmDifferent(){
        $user = $this->createUserAndTeams();
        $passwordBefore = Hash::make("password");
        $user->password = $passwordBefore;
        $user->update();
        $controller = new UserController();
        $request = Request::create('/changepassword', 'POST', [
            'oldPassword' => "password",
            'newPassword' => "newPassword",
            'newPasswordConfirm' => "newPasswordConfirm",
        ]);
        $this->expectException(ValidationException::class);
        $response = $controller->changePassword($request);
    }

    public function testChangePassword(){
        $user = $this->createUserAndTeams();
        $passwordBefore = Hash::make("password");
        $user->password = $passwordBefore;
        $user->update();
        $controller = new UserController();
        $newPassword = "newPassword";
        $request = Request::create('/changepassword', 'POST', [
            'oldPassword' => "password",
            'newPassword' => $newPassword,
            'newPasswordConfirm' => $newPassword,
        ]);
        $response = $controller->changePassword($request);
        $user->fresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

}
