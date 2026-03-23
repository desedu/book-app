<?php
session_start();
require "../dbconnect.php";

if (isset($_SESSION['id'])) {
    header('Location: ../index.php?page=books');
    exit();
}

$genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();
$error = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $favorite_genres = isset($_POST['favorite_genres']) ? implode(',', $_POST['favorite_genres']) : '';
    
    $form_data = [
        'username' => $username,
        'full_name' => $full_name,
        'email' => $email,
        'favorite_genres' => $_POST['favorite_genres'] ?? []
    ];
    
    if ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 4) {
        $error = 'Пароль должен содержать не менее 4 символов';
    } elseif (empty($username) || empty($full_name) || empty($email)) {
        $error = 'Все поля обязательны для заполнения';
    } else {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM readers WHERE email = ? OR username = ?");
            $check_stmt->execute([$email, $username]);
            if ($check_stmt->fetch()) {
                $error = 'Пользователь с таким email или логином уже существует';
            } else {
                $hashed_password = md5($password);
                $insert_stmt = $pdo->prepare("INSERT INTO readers (username, full_name, email, password, favorite_genres) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->execute([$username, $full_name, $email, $hashed_password, $favorite_genres]);
                $_SESSION['msg'] = 'Регистрация успешно завершена! Войдите в систему.';
                header('Location: ../login.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Ошибка регистрации: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - BookCrossing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0">Регистрация</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-center text-muted">Создайте аккаунт в системе буккроссинга</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Логин *</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" required>
                                <small class="text-muted">Уникальное имя пользователя</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">ФИО *</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Пароль *</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Минимум 4 символа</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Подтвердите пароль *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Любимые жанры (можно выбрать несколько)</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($genres as $genre): ?>
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   name="favorite_genres[]" 
                                                   value="<?= $genre['id'] ?>" 
                                                   class="form-check-input"
                                                   id="genre_<?= $genre['id'] ?>"
                                                   <?= (isset($form_data['favorite_genres']) && in_array($genre['id'], $form_data['favorite_genres'])) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="genre_<?= $genre['id'] ?>">
                                                <?= htmlspecialchars($genre['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted">Выберите жанры, которые вам интересны (необязательно)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">Зарегистрироваться</button>
                            <div class="text-center mt-3">
                                <p class="mb-0">Уже есть аккаунт? <a href="../login.php">Войти</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>