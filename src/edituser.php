<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_GET['id'] ?? $_SESSION['id'];

if ($user_id != $_SESSION['id']) {
    $_SESSION['msg'] = "Вы можете редактировать только свой профиль";
    header('Location: ../index.php?page=users');
    exit();
}

$user_stmt = $pdo->prepare("SELECT * FROM readers WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

if (!$user) {
    $_SESSION['msg'] = "Пользователь не найден";
    header('Location: ../index.php?page=users');
    exit();
}

$genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();
$current_genres = !empty($user['favorite_genres']) ? explode(',', $user['favorite_genres']) : [];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $favorite_genres = isset($_POST['favorite_genres']) ? implode(',', $_POST['favorite_genres']) : '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($full_name) || empty($email)) {
        $error = 'ФИО и Email обязательны для заполнения';
    } else {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM readers WHERE email = ? AND id != ?");
            $check_stmt->execute([$email, $user_id]);
            if ($check_stmt->fetch()) {
                $error = 'Этот email уже используется другим пользователем';
            } else {
                $update_sql = "UPDATE readers SET full_name = ?, email = ?, favorite_genres = ? WHERE id = ?";
                $params = [$full_name, $email, $favorite_genres, $user_id];
                
                if (!empty($new_password)) {
                    if (strlen($new_password) < 4) {
                        $error = 'Пароль должен содержать не менее 4 символов';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'Пароли не совпадают';
                    } else {
                        $update_sql = "UPDATE readers SET full_name = ?, email = ?, favorite_genres = ?, password = ? WHERE id = ?";
                        $params = [$full_name, $email, $favorite_genres, md5($new_password), $user_id];
                    }
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute($params);
                    $success = "Профиль успешно обновлен!";
                    $_SESSION['firstname'] = $full_name;
                    $user['full_name'] = $full_name;
                    $user['email'] = $email;
                    $user['favorite_genres'] = $favorite_genres;
                    $current_genres = explode(',', $favorite_genres);
                }
            }
        } catch (PDOException $e) {
            $error = "Ошибка обновления: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать профиль - BookCrossing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Редактировать профиль</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Логин (нельзя изменить)</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <small class="text-muted">Логин нельзя изменить после регистрации</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">ФИО *</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
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
                                                   <?= in_array($genre['id'], $current_genres) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="genre_<?= $genre['id'] ?>">
                                                <?= htmlspecialchars($genre['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            <h6>Сменить пароль (оставьте пустым, если не хотите менять)</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Новый пароль</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Введите новый пароль">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Подтвердите новый пароль</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Подтвердите новый пароль">
                            </div>
                            
                            <button type="submit" class="btn btn-success">Сохранить изменения</button>
                            <a href="../index.php?page=users" class="btn btn-secondary">Отмена</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>