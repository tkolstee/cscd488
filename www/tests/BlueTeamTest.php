<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \BlueTeam
 */
class BlueTeamTest extends TestCase {

    //Test Settings
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

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
     */
    public function testCreateValidBlueTeam(): void {
        self::assertTrue($this->blueTeam->createBlueTeam("test"));
        self::assertEquals("test", $this->blueTeam->getBlueName());
    }

}
