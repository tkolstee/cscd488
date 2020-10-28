<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \User
 */
class UserTest extends TestCase {
    protected $user;

    public function setUp(): void {
        $this->user = new User();
    }

    public function tearDown(): void {
        $db = Db::getInstance();
        $stmt = $db->getConn()->prepare('DELETE FROM users');
        $stmt->execute();
    }

    /**
     * @covers ::createUser
     */
    public function testCreateValidUser(): void {
        $this->assertTrue($this->user->createUser("test","testPass"));
    }

    /**
     * @covers ::createUser
     * @runInSeparateProcess
     */
    public function testCreateUserEmptyFields(): void {
        $this->assertFalse($this->user->createUser("",""));
        $this->assertFalse($this->user->createUser("name",""));
        $this->assertFalse($this->user->createUser("","pass"));
    }

    /**
     * @covers ::createUser
     * @runInSeparateProcess
     */
    public function testCreateUserNameTaken(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->createUser("test","testPass"));
        $this->assertFalse($this->user->createUser("test","123"));
    }

    /**
     * @covers ::createUser
     * @runInSeparateProcess
     */
    public function testCreateUserNull(): void {
        $this->AssertFalse($this->user->createUser(null, null));
        $this->AssertFalse($this->user->createUser(null, "pass"));
        $this->AssertFalse($this->user->createUser("uname", null));
    }

    /**
     * @covers ::validateUser
     * @runInSeparateProcess
     */
    public function testValidateUser(): void {
        $this->assertFalse($this->user->validateUser("test","testPass"));
        $this->user->createUser("test","testPass");
        $this->assertTrue($this->user->validateUser("test","testPass"));
    }

    /**
     * @covers ::validateUser
     * @runInSeparateProcess
     */
    public function testValidateUserEmptyFields(): void {
        $this->assertFalse($this->user->validateUser("",""));
        $this->assertFalse($this->user->validateUser("name",""));
        $this->assertFalse($this->user->validateUser("","pass"));
    }

    /**
     * @covers ::validateUser
     * @runInSeparateProcess
     */
    public function testValidateUserInvalidCredentials(): void {
        $this->assertFalse($this->user->validateUser("test","wrongPass"));
        $this->assertFalse($this->user->validateUser("noAccount","testPass"));
    }

     /**
     * @covers ::validateUser
     * @runInSeparateProcess
     */
    public function testValidateUserNull(): void {
        $this->AssertFalse($this->user->validateUser(null, null));
        $this->AssertFalse($this->user->validateUser(null, "pass"));
        $this->AssertFalse($this->user->validateUser("uname", null));
    }

    /**
     * @covers ::changePassword
     */
    public function testChangePassword(): void {
        $this->user->createUser("test","testPass");
        $this->assertTrue($this->user->changePassword("testPass","test2"));
    }

    /**
     * @covers ::changePassword
     * @runInSeparateProcess
     */
    public function testChangePasswordNotUser(): void {
        $this->assertFalse($this->user->changePassword("testPass","test2"));
    }

    /**
     * @covers ::changePassword
     * @runInSeparateProcess
     */
    public function testChangePasswordEmpty(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->changePassword("testPass",""));
    }

    /**
     * @covers ::changePassword
     * @runInSeparateProcess
     */
    public function testChangePasswordToSame(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->changePassword("testPass","testPass"));
    }

    /**
     * @covers ::changePassword
     * @runInSeparateProcess
     */
    public function testChangePasswordWrongOldPassword(): void {
        $this->user->createUser("test","testPass");
        $this->assertFalse($this->user->changePassword("wrongpassword","test2"));
    }

    /**
     * @covers ::changePassword
     * @runInSeparateProcess
     */
    public function testChangePasswordNull(): void {
        $this->user->createUser("test","testPass");
        $this->AssertFalse($this->user->changePassword("testPass", null));
    }
}
