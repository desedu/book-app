<?php
session_start();
require "dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}
?>
<h1>Книги, которые у меня</h1>

<?php
$stmt = $pdo->prepare("
    SELECT b.*, g.name as genre_name, h.movement_date, 
           l.name as from_location_name
    FROM books b
    LEFT JOIN genres g ON b.genre_id = g.id
    INNER JOIN history h ON h.book_id = b.id
    LEFT JOIN locations l ON l.id = h.from_location_id
    WHERE h.reader_id = ? 
    AND h.action_type = 'получил'
    AND h.id NOT IN (
        SELECT previous_movement_id FROM history 
        WHERE previous_movement_id IS NOT NULL AND action_type = 'вернул'
    )
    ORDER BY h.movement_date DESC
");
$stmt->execute([$_SESSION['id']]);
$books = $stmt->fetchAll();

if (count($books) == 0):
?>
    <div class="alert alert-info">У вас нет книг. Возьмите книгу из списка!</div>
<?php else: ?>
    <?php foreach ($books as $book): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <?php if ($book['cover_image'] && file_exists($book['cover_image'])): ?>
                            <img src="<?= htmlspecialchars($book['cover_image']) ?>" style="max-height: 100px;" alt="">
                        <?php else: ?>
                            <img src="assets/book.png" style="max-height: 100px;" alt="Book">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-7">
                        <h5><?= htmlspecialchars($book['title']) ?></h5>
                        <p class="mb-1">Автор: <?= htmlspecialchars($book['author']) ?></p>
                        <p class="mb-1">Жанр: <?= htmlspecialchars($book['genre_name'] ?? '-') ?></p>
                        <p class="mb-1">Состояние: <?= htmlspecialchars($book['condition_book']) ?></p>
                        <p class="mb-1">Взята: <?= date('d.m.Y', strtotime($book['movement_date'])) ?></p>
                        <p class="mb-1">Взята из: <?= htmlspecialchars($book['from_location_name'] ?? 'Неизвестно') ?></p>
                        <a href="index.php?page=history&book_id=<?= $book['id'] ?>" class="btn btn-sm btn-info">История</a>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="./src/returnbook.php?book_id=<?= $book['id'] ?>" class="btn btn-warning">Вернуть книгу</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>