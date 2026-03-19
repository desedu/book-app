<?php
session_start(["use_strict_mode" => true]);
require_once 'dbconnect.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $login = $_POST['login'];
    $password = md5($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM readers WHERE email = ? AND password = ?");
    $stmt->execute([$login, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['firstname'] = $user['full_name'];
        header('Location: ../index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Неверный email или пароль';
        header('Location: ../login.php');
        exit;
    }
}

header('Location: ../login.php');
exit;