<?php
  
  require_once "../classes/User.php";

  if(isset($_POST['loginSubmit'])) {
    // Eventually need to actually authenticate the user first
    $user = new User();
    if($user->validateUser($_POST['username'], $_POST['password'])){
      $_SESSION['username'] = $_POST['username'];
      $_SESSION['user'] = serialize($user);
    }
    
  }
  if(isset($_POST['signupSubmit'])) {
    $user = new User();
    if($user->createUser($_POST['username'], $_POST['password'])){
      $_SESSION['username'] = $_POST['username'];
      $_SESSION['user'] = serialize($user);
    }
    
  }

  
  
  if (!isset($_SESSION['username'])) {
    if(!empty($_GET['error'])){
      echo($_GET['error']);
    }
?>
      <div id="loginform">
        <form method="POST">

          <?php //Errorhandling on username
          if(!empty($_GET['uname'])) echo "<input type=\"text\" name=\"username\" value=\"".$uname."\"/><br>";
          else echo "<input type=\"text\" name=\"username\" placeholder=\"Username\"/><br>";
          ?>
          <input type="password" name="password" placeholder="Password"/><br>
          <button type="submit" name="loginSubmit">Login</button>
        </form>
      </div>
      <div id="signupform">
        <form method="POST">
          <input type="text" name="username" placeholder="Username"/><br>
          <input type="password" name="password" placeholder="Password"/><br>
          <button type="submit" name="signupSubmit">Sign Up</button>
        </form>
      </div>
<?php
    exit();
  }
  
?>
