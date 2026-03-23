<?php
session_start();
require "dbconnect.php";

if (isset($_SESSION['id'])) {
    header('Location: index.php?page=books');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = md5($_POST['password']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM readers WHERE email = ? AND password = ?");
        $stmt->execute([$login, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['firstname'] = $user['full_name'];
            $_SESSION['login'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php?page=books');
            exit();
        } else {
            $error = 'Неверный email или пароль';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - BookCrossing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">BookCrossing</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-center text-muted">Войдите в систему</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="login" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Войти</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">Нет аккаунта? <a href="src/insertuser.php">Зарегистрироваться</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>