<?php 
  session_start();
  spl_autoload_register(function($class_name) {
    include __DIR__."/../classes/${class_name}.php";
  });
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Finding a Middle Ground</title>
  </head>
  <body>
    <?php
      include "check_login.php";
      include "navmenu.php";
    ?>
