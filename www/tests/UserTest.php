<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \User
 */
class UserTest extends TestCase {
    protected $user;

    /**
     * @covers ::createUser
     */
    public function testCreateUser(): void {
        $unameIn = "test";
        $upassIn = "testPass";
        self::assertTrue(true);
    }

    /**
     * @covers ::validateUser
     */
    public function testValidateUser(): void {
        self::assertTrue(true);
    }
}
