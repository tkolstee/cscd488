<?php
    require "../includes/mainHeader.php";
?>

    <h1>this is the main page</h1>
    <h2>Select a team to play as</h2>
    <div class="container">
        <div class="row">
            <input type ="button" onclick="window.location='/blue.php'" class="Redirect" value="Blue Team"/>
            <input type ="button" onclick="window.location='/red.php'" class="Redirect" value="Red Team"/>
        </div>
    </div>
    
<?php
    include "../includes/changePassword.php";
    require "../includes/mainFooter.php";
?>
