<?php
    require "Db.php";
    class User{

        private $uid = null;
        private $uname = null;
        private $blueID = null;
        private $redID = null;

        public function getUid(){ return $uid; }
        public function getUname(){ return $uname; }
        public function getBlueID(){ return $blueID; }
        public function getRedID(){ return $redID; }

        public function setUid($uidIn){ $this->$uid = $uidIn; }
        public function setUname($unameIn){ $this->$uname = $unameIn; }
        public function setBlueID($blueIDIn){ $this->$blueID = $blueIDIn; }
        public function setRedID($redIDIn){ $this->$redID = $redIDIn; }

        
        public function validateUser($unameIn, $upassIn){
            if(empty($unameIn) || empty($upassIn)){
                header("Location: /index.php?error=emptyFields&uname=".$unameIn);
                return false;
            }
            $row = User::getUser($unameIn);
            if ($row != null) {
                if (password_verify($upassIn, $row[2])) {
                    $this->$uid = $row[0];
                    $this->$uname = $row[1];
                    return true;
              }
              header("Location: /index.php?error=passwordIncorrect&uname=".$unameIn);
              return false;
            }else{
                header("Location: /index.php?error=unameIncorrect");
                return false;
            }
            
        }

        public function createUser($unameIn, $upassIn){
            if(empty($unameIn) || empty($upassIn)){
                header("Location: /index.php?error=emptyFields&uname=".$unameIn);
                return false;
            }
            if ( User::getUser($unameIn) != null ) {
                header("Location: /index.php?error=usernameTaken&uname=".$unameIn);
                return false;
            }
            $db = Db::getInstance();
            $stmt = $db->getConn()->prepare('INSERT INTO users (uname, upassword) VALUES (:uname, :upass)');
            $stmt->bindValue(':uname', $unameIn, SQLITE3_TEXT);
            $stmt->bindValue(':upass', password_hash($upassIn, PASSWORD_BCRYPT),   SQLITE3_TEXT);
            $stmt->execute();
            if(User::getUser($unameIn) == null){
                header("Location: /index.php?error=userNotCreated&uname=".$unameIn);
                return false;
            }
            return $this->validateUser($unameIn, $upassIn);
        }

        public function changePassword($upassIn, $upassNew){
            if(empty($upassIn) || empty($upassNew)){
                header("Location: /profile.php?error=emptyFields");
                return false;
            }
            if($upassIn == $upassNew){
                header("Location: /profile.php?error=samePasswords");
                return false;
            }
            $row = User::getUser($this->$uname);
            if($row != null){
                if (password_verify($upassIn, $row[2])) {
                    $db = Db::getInstance();
                    $stmt = $db->getConn()->prepare('UPDATE users SET upassword = :upassNew WHERE uname=:key');
                    $stmt->bindValue(':key', $this->$uname, SQLITE3_TEXT);
                    $stmt->bindValue(':upassNew', password_hash($upassNew, PASSWORD_BCRYPT), SQLITE3_TEXT);
                    $stmt->execute();
                }else{
                    header("Location: /profile.php?error=passwordIncorrect");
                    return false;
                }
            }else{
                header("Location: /profile.php?error=userNotFound");
                return false;
            }
            //validate password is changed
            $row = User::getUser($this->$uname);
            if(password_verify($upassNew, $row[2])) return true;
            else{
                header("Location: /profile.php?error=passwordUnchanged");
                return false;
            }
        }

        private static function getUser($unameIn){
            $db = Db::getInstance();
            $stmt = $db->getConn()->prepare('SELECT * FROM users WHERE uname=:key');
            $stmt->bindValue(':key', $unameIn, SQLITE3_TEXT);
            $result = $stmt->execute();
            if ($row = $result->fetchArray()){
                return $row;
            }else{
                return null;
            }
        }

    }

?>
