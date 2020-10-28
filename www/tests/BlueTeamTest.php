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
        $this->assertTrue($this->blueTeam->createBlueTeam("test"));
    }

    /**
     * @covers ::createBlueTeam
     */
    public function testCreateBlueTeamEmptyFields(): void {
        $this->assertFalse($this->blueTeam->createBlueTeam(""));
    }

    /**
     * @covers ::createBlueTeam
     */
    public function testCreateBlueTeamNameTaken(): void {
        $this->blueTeam->createBlueTeam("team1");
        $this->assertFalse($this->blueTeam->createBlueTeam("team1"));
    }

    /**
     * @covers ::createBlueTeam
     */
    public function testCreateBlueTeamNull(): void {
        $this->assertFalse($this->blueTeam->createBlueTeam(null));
    }
}
