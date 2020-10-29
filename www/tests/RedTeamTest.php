<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \RedTeam
 */
class RedTeamTest extends TestCase {

    //Test Settings
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

    protected $redTeam;

    public function setUp(): void {
        $this->redTeam = new RedTeam();
    }

    public function tearDown(): void {
        $db = Db::getInstance();
        $stmt = $db->getConn()->prepare('DELETE FROM redteam');
        $stmt->execute();
    }

    /**
     * @covers ::createRedTeam
     */
    public function testCreateValidRedTeam(): void {
        $this->assertTrue($this->redTeam->createRedTeam("test"));
    }

    /**
     * @covers ::createRedTeam
     */
    public function testCreateRedTeamEmptyFields(): void {
        $this->assertFalse($this->redTeam->createRedTeam(""));
    }

    /**
     * @covers ::createRedTeam
     */
    public function testCreateRedTeamNameTaken(): void {
        $this->redTeam->createRedTeam("team1");
        $this->assertFalse($this->redTeam->createRedTeam("team1"));
    }

    /**
     * @covers ::createRedTeam
     */
    public function testCreateRedTeamNull(): void {
        $this->assertFalse($this->redTeam->createRedTeam(null));
    }
}
