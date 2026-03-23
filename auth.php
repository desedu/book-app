<?php
session_start();

if (isset($_GET["logout"])) {
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit();
}

if (isset($_POST["login"]) && $_POST["login"] != '') {
    require "dbconnect.php";
    
    try {
        $sql = 'SELECT * FROM readers WHERE email = :login';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':login', $_POST['login']);
        $stmt->execute();
    } catch (PDOException $error) {
        $msg = "Ошибка аутентификации: " . $error->getMessage();
    }
    
    if ($row = $stmt->fetch(PDO::FETCH_LAZY)) {
        if (md5($_POST["password"]) != $row['password']) {
            $msg = "Неправильный пароль!";
        } else {
            $_SESSION['login'] = $_POST["login"];
            $_SESSION['firstname'] = $row['full_name'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $msg = "Вы успешно вошли в систему";
            header('Location: index.php');
            exit();
        }
    } else {
        $msg = "Неправильное имя пользователя!";
    }
}
?>