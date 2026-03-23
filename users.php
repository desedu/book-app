<?php
session_start();
require "dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}
?>
<h1>Пользователи системы</h1>

<div class="mb-3">
    <a href="./src/edituser.php" class="btn btn-success">Редактировать мой профиль</a>
</div>

<?php
$stmt = $pdo->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM books b 
            WHERE b.id IN (
                SELECT book_id FROM history h 
                WHERE h.reader_id = r.id AND h.action_type = 'получил'
                AND h.id NOT IN (
                    SELECT previous_movement_id FROM history 
                    WHERE previous_movement_id IS NOT NULL
                )
            )) as books_taken,
           (SELECT COUNT(*) FROM reviews WHERE reader_id = r.id) as reviews_count
    FROM readers r 
    ORDER BY r.registration_date DESC
");
$stmt->execute();
$users = $stmt->fetchAll();

foreach ($users as $user):
    $favorite_genres = [];
    if (!empty($user['favorite_genres'])) {
        $genre_ids = explode(',', $user['favorite_genres']);
        $placeholders = implode(',', array_fill(0, count($genre_ids), '?'));
        $genre_stmt = $pdo->prepare("SELECT name FROM genres WHERE id IN ($placeholders)");
        $genre_stmt->execute($genre_ids);
        $favorite_genres = $genre_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5><?= htmlspecialchars($user['full_name']) ?>
                        <?php if ($user['id'] == $_SESSION['id']): ?>
                            <span class="badge bg-primary">Это вы</span>
                        <?php endif; ?>
                    </h5>
                    <p class="mb-1 text-muted">@<?= htmlspecialchars($user['username']) ?></p>
                    <p class="mb-1">Email: <?= htmlspecialchars($user['email']) ?></p>
                    <p class="mb-1">Зарегистрирован: <?= date('d.m.Y', strtotime($user['registration_date'])) ?></p>
                    <p class="mb-1">Взято книг: <?= $user['books_taken'] ?></p>
                    <p class="mb-1">Отзывов: <?= $user['reviews_count'] ?></p>
                    <?php if (!empty($favorite_genres)): ?>
                        <p class="mb-0">
                            Любимые жанры:
                            <?php foreach ($favorite_genres as $genre): ?>
                                <span class="badge bg-info me-1"><?= htmlspecialchars($genre) ?></span>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>