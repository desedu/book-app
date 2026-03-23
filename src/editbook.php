<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$book_stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$book_stmt->execute([$id]);
$book = $book_stmt->fetch();

if (!$book) {
    $_SESSION['msg'] = "Книга не найдена";
    header('Location: ../index.php?page=books');
    exit();
}

$genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_image = $book['cover_image'];
    
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if ($cover_image && file_exists($cover_image)) {
            unlink($cover_image);
        }
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = 'book_' . uniqid() . '.' . $ext;
        $filepath = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $filepath)) {
            $cover_image = 'uploads/' . $filename;
        }
    }
    
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, genre_id = ?, condition_book = ?, cover_image = ? WHERE id = ?");
    $stmt->execute([$_POST['title'], $_POST['author'], $_POST['genre_id'], $_POST['condition_book'], $cover_image, $id]);
    
    $_SESSION['msg'] = "Книга успешно обновлена";
    header('Location: ../index.php?page=books');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать книгу</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Редактировать книгу</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Название</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Автор</label>
                                <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Жанр</label>
                                <select name="genre_id" class="form-select" required>
                                    <?php foreach ($genres as $genre): ?>
                                        <option value="<?= $genre['id'] ?>" <?= $genre['id'] == $book['genre_id'] ? 'selected' : '' ?>>
                                            <?= $genre['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Состояние</label>
                                <select name="condition_book" class="form-select">
                                    <option value="отличное" <?= $book['condition_book'] == 'отличное' ? 'selected' : '' ?>>Отличное</option>
                                    <option value="хорошее" <?= $book['condition_book'] == 'хорошее' ? 'selected' : '' ?>>Хорошее</option>
                                    <option value="удовлетворительное" <?= $book['condition_book'] == 'удовлетворительное' ? 'selected' : '' ?>>Удовлетворительное</option>
                                    <option value="плохое" <?= $book['condition_book'] == 'плохое' ? 'selected' : '' ?>>Плохое</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Новая обложка</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <small class="text-muted">Оставьте пустым, чтобы сохранить текущую обложку</small>
                            </div>
                            <?php if ($book['cover_image'] && file_exists('../' . $book['cover_image'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">Текущая обложка</label>
                                    <div>
                                        <img src="<?= htmlspecialchars('../' . $book['cover_image']) ?>" style="max-height: 150px;" alt="">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                            <a href="../index.php?page=books" class="btn btn-secondary">Отмена</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>