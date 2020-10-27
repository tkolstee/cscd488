<?php
    if(!empty($_GET['error'])){
        echo $_GET['error'];
    }
    if(isset($_POST['passwordChangeSubmit'])){
        $user = unserialize($_SESSION['user']);
        echo $user->getUname();
        if($user->changePassword( $_POST['password'], $_POST['passwordNew']))
        echo "Password Changed Successfully";
    }if(isset($_POST['passwordChange'])){
?>
    <div id="passwordChangeform">
    <form method="POST">
    <input type="password" name="password" placeholder="Current Password"/><br>
    <input type="password" name="passwordNew" placeholder="New Password"/><br>
    <button type="submit" name="passwordChangeSubmit">Change Password</button>
    </form>
    </div>
<?php 
    }else{
?>
    <div id="passwordChange">
    <form method="POST">
    <button type="submit" name="passwordChange">Change Password</button>
    </form>
<?php }
?>
