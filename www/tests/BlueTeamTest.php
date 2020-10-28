<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \BlueTeam
 */
class BlueTeamTest extends TestCase {
    protected $blueTeam;

    public function setUp(): void {
        $this->blueTeam = new BlueTeam();
    }

    public function tearDown(): void {
        $db = Db::getInstance();
        $stmt = $db->getConn()->prepare('DELETE FROM blueteam');
        $stmt->execute();
    }

    /**
     * @covers ::createBlueTeam
     * @runInSeparateProcess
     */
    public function testCreateBlueTeam(): void {
        self::assertTrue($blueTeam->createBlueTeam("test"));
    }

}
