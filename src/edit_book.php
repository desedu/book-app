<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$book = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$book->execute([$id]);
$book = $book->fetch();

if (!$book) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_image = $book['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $upload_dir_i = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if ($cover_image && file_exists($cover_image)) {
            unlink($cover_image);
        }
        $extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $filepath)) {
            $cover_image = $upload_dir_i . $filename;
        }
    }
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, genre_id = ?, condition_book = ?, cover_image = ? WHERE id = ?");
    $stmt->execute([$_POST['title'], $_POST['author'], $_POST['genre_id'], $_POST['condition_book'], $cover_image, $id]);
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE HTML>
<html lang="RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать книгу</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Редактировать книгу</h6>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label small">Название</label>
                                        <input type="text" name="title" class="form-control form-control-sm" value="<?= htmlspecialchars($book['title']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Автор</label>
                                        <input type="text" name="author" class="form-control form-control-sm" value="<?= htmlspecialchars($book['author']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Жанр</label>
                                        <select name="genre_id" class="form-select form-select-sm" required>
                                            <?php
                                            $genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();
                                            foreach ($genres as $genre) {
                                                $selected = $genre['id'] == $book['genre_id'] ? 'selected' : '';
                                                echo "<option value='{$genre['id']}' $selected>{$genre['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Состояние</label>
                                        <select name="condition_book" class="form-select form-select-sm">
                                            <option value="отличное" <?= $book['condition_book'] == 'отличное' ? 'selected' : '' ?>>Отличное</option>
                                            <option value="хорошее" <?= $book['condition_book'] == 'хорошее' ? 'selected' : '' ?>>Хорошее</option>
                                            <option value="удовлетворительное" <?= $book['condition_book'] == 'удовлетворительное' ? 'selected' : '' ?>>Удовлетворительное</option>
                                            <option value="плохое" <?= $book['condition_book'] == 'плохое' ? 'selected' : '' ?>>Плохое</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Новая обложка</label>
                                        <input type="file" name="cover_image" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <?php if ($book['cover_image'] && file_exists("../{$book['cover_image']}")): ?>
                                        <div class="text-center p-3 bg-light rounded">
                                            <label class="form-label small">Текущая обложка</label>
                                            <img src="<?= htmlspecialchars("../{$book['cover_image']}") ?>" class="img-fluid rounded" alt="">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                            <a href="../index.php" class="btn btn-secondary btn-sm">Отмена</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>