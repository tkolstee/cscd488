<?php
    require "Db.php";
    class User{

        private $uid = null;
        private $uname = null;

        public function getUid(){ return $uid; }
        public function getUname(){ return $uname; }
        public function setUid($uidIn){ $uid = $uidIn; }
        public function setUname($unameIn){ $uname = $unameIn; }
        
        public function validateUser($unameIn, $upassIn){
            if(empty($unameIn) || empty($upassIn)){
                header("Location: /index.php?error=emptyFields&uname=".$unameIn);
                exit();
            }
            $row = User::getUser($unameIn);
            if ($row != null) {
                if (password_verify($upassIn, $row[2])) {
                    $this->$uid = $row[0];
                    $this->$uname = $row[1];
                    return true;
              }
              header("Location: /index.php?error=passwordIncorrect&uname=".$unameIn);
              exit();
            }else{
                header("Location: /index.php?error=unameIncorrect");
                exit();
            }
            
        }

        public function createUser($unameIn, $upassIn){
            if(empty($unameIn) || empty($upassIn)){
                header("Location: /index.php?error=emptyFields&uname=".$unameIn);
                exit();
            }
            $db = Db::getInstance();
            if ( User::getUser($unameIn) != null ) {
                header("Location: /index.php?error=usernameTaken&uname=".$unameIn);
                exit();
            }
            $stmt = $db->getConn()->prepare('INSERT INTO users (uname, upassword) VALUES (:uname, :upass)');
            $stmt->bindValue(':uname', $unameIn, SQLITE3_TEXT);
            $stmt->bindValue(':upass', password_hash($upassIn, PASSWORD_BCRYPT),   SQLITE3_TEXT);
            $stmt->execute();
            if(User::getUser($unameIn) == null){
                header("Location: /index.php?error=userNotCreated&uname=".$unameIn);
                exit();
            }
            return $this->validateUser($unameIn, $upassIn);
        }

        public function changePassword($upassIn, $upassNew){
            if(empty($upassIn) || empty($upassNew)){
                header("Location: /profile.php?error=emptyFields");
                exit();
            }
            if($upassIn == $upassNew){
                header("Location: /profile.php?error=samePasswords");
                exit();
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
                    exit();
                }
            }else{
                header("Location: /profile.php?error=userNotFound");
                exit();
            }
            //validate password is changed
            $row = User::getUser($this->$uname);
            if(password_verify($upassNew, $row[2])) return true;
            else{
                header("Location: /profile.php?error=passwordUnchanged");
                exit();
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
