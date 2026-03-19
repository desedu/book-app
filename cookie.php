<?php
if (isset($_GET['logout'])) {
    setcookie("firstname", "", time() - 3600, "/");
    header('Location: ../index.php');
    exit;
}

if (isset($_GET['login'])) {
    setcookie("firstname", $_GET['login'], time() + 15000, "/");
    header('Location: ../index.php');
    exit;
}

