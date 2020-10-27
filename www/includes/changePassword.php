<?php

    if(isset($_POST['passwordChangeSubmit'])){
        $user = unserialize($_SESSION['user']);
        echo $user->getUname();
        if($user->changePassword( $_POST['password'], $_POST['passwordNew']))
        echo "Password Changed Successfully";
    }
?>
    <div id="passwordChangeform">
    <form method="POST">
    <input type="password" name="password" placeholder="Current Password"/><br>
    <input type="password" name="passwordNew" placeholder="New Password"/><br>

    <button type="submit" name="passwordChangeSubmit">Change Password</button>
    </form>
    </div>
