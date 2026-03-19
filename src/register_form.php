<?php
session_start(["use_strict_mode" => true]);
require_once '../dbconnect.php';

$genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_reader'])) {
    $form_data = $_POST;
    
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            setcookie($key, implode(',', $value), time() + 86400 * 30, "/");
        } else {
            setcookie($key, $value, time() + 86400 * 30, "/");
        }
    }
    
    $password = md5($_POST['password']);
    $favorite_genres = isset($_POST['favorite_genres']) ? implode(',', $_POST['favorite_genres']) : '';
    
    $stmt = $pdo->prepare("INSERT INTO readers (username, full_name, email, favorite_genres, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['username'], $_POST['full_name'], $_POST['email'], $favorite_genres, $password]);
    
    $show_result = true;
} else {
    $allowed_fields = ['username', 'full_name', 'email', 'favorite_genres'];
    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field])) {
            $form_data[$field] = $_POST[$field];
        } elseif (isset($_COOKIE[$field])) {
            $form_data[$field] = $_COOKIE[$field];
        }
    }
    $show_result = false;
}

$selected_genres = isset($form_data['favorite_genres']) ? explode(',', $form_data['favorite_genres']) : [];
?>
<!DOCTYPE HTML>
<html lang="RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация читателя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .genres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
        }
        .genre-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .genre-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Регистрация читателя</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($show_result): 
                            $selected_genre_names = [];
                            if (!empty($_POST['favorite_genres'])) {
                                $placeholders = implode(',', array_fill(0, count($_POST['favorite_genres']), '?'));
                                $stmt = $pdo->prepare("SELECT name FROM genres WHERE id IN ($placeholders)");
                                $stmt->execute($_POST['favorite_genres']);
                                $selected_genre_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            }
                        ?>
                        <div class="alert alert-success mb-4">
                            <h5>Читатель успешно зарегистрирован!</h5>
                            <p><strong>Введенные данные:</strong></p>
                            <table class="table table-sm">
                                <tr>
                                    <th>Логин:</th>
                                    <td><?= htmlspecialchars($_POST['username'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>ФИО:</th>
                                    <td><?= htmlspecialchars($_POST['full_name'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($_POST['email'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Любимые жанры:</th>
                                    <td>
                                        <?php if (!empty($selected_genre_names)): ?>
                                            <?php foreach ($selected_genre_names as $genre): ?>
                                                <span class="badge bg-info me-1"><?= htmlspecialchars($genre) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Не указаны</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Логин</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">ФИО</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Любимые жанры (можно выбрать несколько)</label>
                                <div class="genres-grid">
                                    <?php foreach ($genres as $genre): ?>
                                    <div class="genre-item">
                                        <input type="checkbox" 
                                               name="favorite_genres[]" 
                                               value="<?= $genre['id'] ?>" 
                                               id="genre_<?= $genre['id'] ?>"
                                               <?= in_array($genre['id'], $selected_genres) ? 'checked' : '' ?>>
                                        <label for="genre_<?= $genre['id'] ?>"><?= htmlspecialchars($genre['name']) ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <button type="submit" name="register_reader" class="btn btn-primary">Зарегистрироваться</button>
                            <a href="../index.php" class="btn btn-secondary">На главную</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>