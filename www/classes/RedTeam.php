<?php
    require_once "Db.php";
    class RedTeam{

        private $redID = null;
        private $redName = null;
        
        public function getRedID(){ return $redID; }
        public function getRedName(){ return $redName; }
        
        public function setRedID($redIDIn){ $this->$redID = $redIDIn; }
        public function setRedName($redNameIn){ $this->$redName = $redNameIn; }

        public function createRedTeam($redNameIn){
            if(empty($redNameIn)){
                header("Location: /red.php?error=emptyName");
                exit();
            }
            if ( RedTeam::getRedTeam($redNameIn) != null ) {
                header("Location: /red.php?error=teamnameTaken&redname=".$redNameIn);
                exit();
            }
            $db = Db::getInstance();
            $stmt = $db->getConn()->prepare('INSERT INTO redteam (redName) VALUES (:redName)');
            $stmt->bindValue(':redName', $redNameIn, SQLITE3_TEXT);
            $stmt->execute();
            $row = RedTeam::getRedTeam($redNameIn);
            if(row == null){
                header("Location: /red.php?error=teamNotCreated&redname=".$redNameIn);
                exit();
            }
            $this->$redID = $row[0];
            $this->$redName = $redNameIn;
            return true;
        }

        private static function getRedTeam($redIDIn){
            $db = Db::getInstance();
            $stmt = $db->getConn()->prepare('SELECT * FROM redteam WHERE redName=:key');
            $stmt->bindValue(':key', $redIDIn, SQLITE3_TEXT);
            $result = $stmt->execute();
            if ($row = $result->fetchArray()){
                return $row;
            }else{
                return null;
            }
        }

    }

?>
