<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
    
    //Test Settings
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

    protected $user;

    public function setUp(): void {
        $this->user = new User();
    }

    public function tearDown(): void {
        $db = Db::getInstance();
        $stmt = $db->getConn()->prepare('DELETE FROM users');
        $stmt->execute();
    }

    public function testCreateValidUser(): void {
        $this->assertTrue($this->user->createUser("test","testPass"));
    }

    public function testCreateUserEmptyFields(): void {
        $this->assertFalse($this->user->createUser("",""));
        $this->assertFalse($this->user->createUser("name",""));
        $this->assertFalse($this->user->createUser("","pass"));
    }

    public function testCreateUserNameTaken(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->createUser("test","testPass"));
        $this->assertFalse($this->user->createUser("test","123"));
    }

    public function testCreateUserNull(): void {
        $this->AssertFalse($this->user->createUser(null, null));
        $this->AssertFalse($this->user->createUser(null, "pass"));
        $this->AssertFalse($this->user->createUser("uname", null));
    }

    public function testValidateUser(): void {
        $this->assertFalse($this->user->validateUser("test","testPass"));
        $this->user->createUser("test","testPass");
        $this->assertTrue($this->user->validateUser("test","testPass"));
    }

    public function testValidateUserEmptyFields(): void {
        $this->assertFalse($this->user->validateUser("",""));
        $this->assertFalse($this->user->validateUser("name",""));
        $this->assertFalse($this->user->validateUser("","pass"));
    }

    public function testValidateUserInvalidCredentials(): void {
        $this->assertFalse($this->user->validateUser("test","wrongPass"));
        $this->assertFalse($this->user->validateUser("noAccount","testPass"));
    }

    public function testValidateUserNull(): void {
        $this->AssertFalse($this->user->validateUser(null, null));
        $this->AssertFalse($this->user->validateUser(null, "pass"));
        $this->AssertFalse($this->user->validateUser("uname", null));
    }

    public function testChangePassword(): void {
        $this->user->createUser("test","testPass");
        $this->assertTrue($this->user->changePassword("testPass","test2"));
    }

    public function testChangePasswordNotUser(): void {
        $this->assertFalse($this->user->changePassword("testPass","test2"));
    }

    public function testChangePasswordEmpty(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->changePassword("testPass",""));
    }

    public function testChangePasswordToSame(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->changePassword("testPass","testPass"));
    }

    public function testChangePasswordWrongOldPassword(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->changePassword("wrongpassword","test2"));
    }

    public function testChangePasswordNull(): void {
        $this->user->createUser("test","testPass");
        $this->AssertFalse($this->user->changePassword("testPass", null));
    }

    public function testSetUnameValid(): void {
        $this->user->createUser("test","testPass");
        $this->AssertTrue($this->user->setUname("newName"));
    }
}
