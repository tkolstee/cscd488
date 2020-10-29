<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class BlueTeamTest extends TestCase {

    //Test Settings
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

    protected $blueTeam;
    protected $leader;

    public function setUp(): void {
        $this->blueTeam = new BlueTeam();
        $this->leader = new User();
        $this->leader->createUser("leader", "pass");
    }

    public function tearDown(): void {
        $db = Db::getInstance();
        $stmt = $db->getConn()->prepare('DELETE FROM blueteam');
        $stmt->execute();
    }

    public function testCreateValidBlueTeam(): void {
        $this->assertTrue($this->blueTeam->createBlueTeam("test", "leader"));
    }

    public function testCreateBlueTeamEmptyFields(): void {
        $this->assertFalse($this->blueTeam->createBlueTeam("",""));
    }

    public function testCreateBlueTeamNameTaken(): void {
        $this->blueTeam->createBlueTeam("team1", "leader");
        $this->assertFalse($this->blueTeam->createBlueTeam("team1", "leader"));
    }

    public function testCreateBlueTeamNull(): void {
        $this->assertFalse($this->blueTeam->createBlueTeam(null, null));
    }
}
