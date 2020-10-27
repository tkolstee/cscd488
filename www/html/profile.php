<?php
    include "../includes/mainHeader.php";
    $user = unserialize($_SESSION['user']);
?>
<h1>Profile</h1><br>
<div class="profileSettings">
    <h2>Username: <?= $user->getUname()?></h2><br>
    <div class="blueText">
        <h3>Blue Team:</h3><br>
            Team Name: <br>
            Revenue: <br>
    </div>
    <div class="redTeam">
        <h3>Red Team:</h3><br>
            Team Name: <br>
            Money: <br>
            Successful Attacks: <br>
    </div>
</div>