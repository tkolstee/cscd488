<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ExampleTest extends TestCase {
    public function testSuccess(): void {
        self::assertTrue(true);
    }

    public function testFailure(): void {
        self::assertTrue(false);
    }
}
