<?php
    require_once "Db.php";
    require_once "User.php";
    class BlueTeam{

        private $blueID = null;
        private $blueName = null;
        private $leaderID = null;
        
        public function getBlueID(){ return $blueID; }
        public function getBlueName(){ return $blueName; }
        public function getLeaderID(){ return $leaderID; }

        public function setBlueID($blueIDIn){ $this->$blueID = $blueIDIn; }
        public function setBlueName($blueNameIn){ $this->$blueName = $blueNameIn; }
        public function setLeaderID($leaderIDIn){ $this->$leaderID = $leaderIDIn; }

        public function createBlueTeam($blueNameIn, $leaderNameIn){
            if(empty($blueNameIn) || empty($leaderNameIn)){
                header("Location: /blue.php?error=emptyFields&bluename=".$blueNameIn."&leadername=".$leaderNameIn);
                return false;
            }
            if(BlueTeam::getBlueTeam($blueNameIn) != null){
                header("Location: /blue.php?error=teamnameTaken&bluename=".$blueNameIn."&leadername=".$leaderNameIn);
                return false;
            }
            $row = User::getUser($leaderNameIn);
            if($row == null){
                header("Location: /blue.php?error=leaderInvalid&bluename=".$blueNameIn."&leadername=".$leaderNameIn);
                return false;
            }
            $leaderIDIn = $row[0];
            $db = Db::getInstance();
            $stmt = $db->getConn()->prepare('INSERT INTO blueteam (blueName, leaderID) VALUES (:blueName, :leaderID)');
            $stmt->bindValue(':blueName', $blueNameIn, SQLITE3_TEXT);
            $stmt->bindValue(':leaderID', $leaderIDIn, SQLITE3_TEXT);
            $stmt->execute();
            $row = BlueTeam::getBlueTeam($blueNameIn);
            if($row == null){
                header("Location: /blue.php?error=teamNotCreated&bluename=".$blueNameIn."&leadername=".$leaderNameIn);
                return false;
            }
            $this->blueID = $row[0];
            $this->$leaderID = $leaderIDIn;
            $this->blueName = $row[1];
            return true;
        }

        private static function getBlueTeam($blueNameIn){
            $db = Db::getInstance();
            $stmt = $db->getConn()->prepare('SELECT * FROM blueteam WHERE blueName=:key');
            $stmt->bindValue(':key', $blueNameIn, SQLITE3_TEXT);
            $result = $stmt->execute();
            if ($row = $result->fetchArray()){
                return $row;
            }else{
                return null;
            }
        }

    }

?>
