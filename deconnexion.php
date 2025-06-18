<?php
    session_start();
    session_destroy();
    header("Location: bienvenue.php");
    exit();
?>

