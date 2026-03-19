<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

include 'cookie.php';

$genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();
?>
<!DOCTYPE HTML>
<html lang="RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Буккроссинг</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Буккроссинг</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-white"><?= htmlspecialchars($_SESSION['firstname']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth.php?logout=1">Выйти</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <ul class="nav nav-tabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#books">Книги</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#readers">Читатели</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#locations">Локации</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Отзывы</button></li>
        </ul>

        <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="books">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col"><h6 class="mb-0">Книги</h6></div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#addBookForm">+ Добавить книгу</button>
                            </div>
                        </div>
                    </div>
                    <div class="collapse" id="addBookForm">
                        <div class="card-body bg-light">
                            <form method="post" action="./src/books_actions.php" enctype="multipart/form-data" class="row g-3">
                                <div class="col-md-2">
                                    <input type="text" name="title" class="form-control form-control-sm" placeholder="Название" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="author" class="form-control form-control-sm" placeholder="Автор" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="genre_id" class="form-select form-select-sm" required>
                                        <option value="">Выберите жанр</option>
                                        <?php foreach ($genres as $genre): ?>
                                            <option value="<?= $genre['id'] ?>"><?= $genre['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="condition_book" class="form-select form-select-sm">
                                        <option value="отличное">Отличное</option>
                                        <option value="хорошее" selected>Хорошее</option>
                                        <option value="удовлетворительное">Удовлетворительное</option>
                                        <option value="плохое">Плохое</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="file" name="cover_image" class="form-control form-control-sm" accept="image/*">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="add_book" class="btn btn-sm btn-success">Сохранить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th></th>
                                    <th>Название</th>
                                    <th>Автор</th>
                                    <th>Жанр</th>
                                    <th>Состояние</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $books = $pdo->query("
                                    SELECT b.*, g.name as genre_name 
                                    FROM books b 
                                    LEFT JOIN genres g ON b.genre_id = g.id
                                    ORDER BY b.id DESC
                                ")->fetchAll();
                                foreach ($books as $book):
                                ?>
                                <tr>
                                    <td><?= $book['id'] ?></td>
                                    <td>
                                        <?php if ($book['cover_image'] && file_exists($book['cover_image'])): ?>
                                            <img src="<?= htmlspecialchars($book['cover_image']) ?>" style="max-height: 40px;" alt="">
                                        <?php else: ?>
                                            <span>📖</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($book['title']) ?></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td><?= $book['genre_name'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $book['condition_book'] == 'отличное' ? 'success' : ($book['condition_book'] == 'хорошее' ? 'info' : ($book['condition_book'] == 'удовлетворительное' ? 'warning' : 'danger')) ?>">
                                            <?= $book['condition_book'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="./src/edit_book.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-warning py-0">✏️</a>
                                        <a href="./src/books_actions.php?delete_book=<?= $book['id'] ?>" class="btn btn-sm btn-danger py-0" onclick="return confirm('Удалить?')">🗑️</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="readers">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col"><h6 class="mb-0">Читатели</h6></div>
                            <div class="col-auto">
                                <a href="./src/register_form.php" class="btn btn-sm btn-success">+ Регистрация читателя</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Логин</th>
                                    <th>ФИО</th>
                                    <th>Email</th>
                                    <th>Любимые жанры</th>
                                    <th>Дата регистрации</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $readers = $pdo->query("SELECT * FROM readers ORDER BY registration_date DESC")->fetchAll();
                                foreach ($readers as $reader):
                                    $favorite_genres = [];
                                    if (!empty($reader['favorite_genres'])) {
                                        $genre_ids = explode(',', $reader['favorite_genres']);
                                        $placeholders = implode(',', array_fill(0, count($genre_ids), '?'));
                                        $stmt = $pdo->prepare("SELECT name FROM genres WHERE id IN ($placeholders)");
                                        $stmt->execute($genre_ids);
                                        $favorite_genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                    }
                                ?>
                                <tr>
                                    <td><?= $reader['id'] ?></td>
                                    <td><?= htmlspecialchars($reader['username']) ?></td>
                                    <td><?= htmlspecialchars($reader['full_name']) ?></td>
                                    <td><?= htmlspecialchars($reader['email']) ?></td>
                                    <td>
                                        <?php if (!empty($favorite_genres)): ?>
                                            <?php foreach ($favorite_genres as $genre): ?>
                                                <span class="badge bg-info me-1"><?= htmlspecialchars($genre) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $reader['registration_date'] ?></td>
                                    <td>
                                        <a href="./src/readers_actions.php?delete_reader=<?= $reader['id'] ?>" class="btn btn-sm btn-danger py-0" onclick="return confirm('Удалить?')">🗑️</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="locations">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col"><h6 class="mb-0">Локации</h6></div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#addLocationForm">+ Добавить локацию</button>
                            </div>
                        </div>
                    </div>
                    <div class="collapse" id="addLocationForm">
                        <div class="card-body bg-light">
                            <form method="post" action="./src/locations_actions.php" class="row g-3">
                                <div class="col-md-5">
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Название" required>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="address" class="form-control form-control-sm" placeholder="Адрес" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="add_location" class="btn btn-sm btn-success">OK</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Адрес</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $locations = $pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();
                                foreach ($locations as $location):
                                ?>
                                <tr>
                                    <td><?= $location['id'] ?></td>
                                    <td><?= htmlspecialchars($location['name']) ?></td>
                                    <td><?= htmlspecialchars($location['address']) ?></td>
                                    <td>
                                        <a href="./src/locations_actions.php?delete_location=<?= $location['id'] ?>" class="btn btn-sm btn-danger py-0" onclick="return confirm('Удалить?')">🗑️</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="reviews">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col"><h6 class="mb-0">Отзывы</h6></div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#addReviewForm">+ Добавить отзыв</button>
                            </div>
                        </div>
                    </div>
                    <div class="collapse" id="addReviewForm">
                        <div class="card-body bg-light">
                            <form method="post" action="./src/reviews_actions.php" class="row g-3">
                                <div class="col-md-3">
                                    <select name="book_id" class="form-select form-select-sm" required>
                                        <option value="">Книга</option>
                                        <?php
                                        $books_list = $pdo->query("SELECT id, title FROM books ORDER BY title")->fetchAll();
                                        foreach ($books_list as $b) {
                                            echo "<option value='{$b['id']}'>{$b['title']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="reader_id" class="form-select form-select-sm" required>
                                        <option value="">Читатель</option>
                                        <?php
                                        $readers_list = $pdo->query("SELECT id, full_name FROM readers ORDER BY full_name")->fetchAll();
                                        foreach ($readers_list as $r) {
                                            echo "<option value='{$r['id']}'>{$r['full_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <select name="rating" class="form-select form-select-sm" required>
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?>⭐</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="comment" class="form-control form-control-sm" placeholder="Комментарий">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="add_review" class="btn btn-sm btn-success">OK</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Книга</th>
                                    <th>Читатель</th>
                                    <th>Оценка</th>
                                    <th>Комментарий</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $reviews = $pdo->query("
                                    SELECT r.*, b.title, rd.full_name 
                                    FROM reviews r
                                    JOIN books b ON r.book_id = b.id
                                    JOIN readers rd ON r.reader_id = rd.id
                                    ORDER BY r.review_date DESC
                                ")->fetchAll();
                                foreach ($reviews as $review):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($review['title']) ?></td>
                                    <td><?= htmlspecialchars($review['full_name']) ?></td>
                                    <td><?= str_repeat('⭐', $review['rating']) ?></td>
                                    <td><?= htmlspecialchars($review['comment']) ?></td>
                                    <td><?= $review['review_date'] ?></td>
                                    <td>
                                        <a href="./src/reviews_actions.php?delete_review=<?= $review['id'] ?>" class="btn btn-sm btn-danger py-0" onclick="return confirm('Удалить?')">🗑️</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>