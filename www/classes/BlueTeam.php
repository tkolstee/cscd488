<?php
    include "Db.php";
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

        public function createBlueTeam($blueNameIn){
            if(empty($blueNameIn)){
                header("Location: /blue.php?error=emptyName");
                return false;
            }
            if ( BlueTeam::getBlueTeam($blueNameIn) != null ) {
                header("Location: /blue.php?error=teamnameTaken&bluename=".$blueNameIn);
                return false;
            }
            $db = Db::getInstance();
            $stmt = $db->getConn()->prepare('INSERT INTO blueteam (blueName) VALUES (:blueName)');
            $stmt->bindValue(':blueName', $blueNameIn, SQLITE3_TEXT);
            $stmt->execute();
            $row = BlueTeam::getBlueTeam($blueNameIn);
            if($row == null){
                header("Location: /blue.php?error=teamNotCreated&bluename=".$blueNameIn);
                return false;
            }
            $this->$blueID = $row[0];
            //$this->$leaderID = $leaderIDIn;
            $this->$blueName = $blueNameIn;
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
