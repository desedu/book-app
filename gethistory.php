<?php
session_start();
require "dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$book_id = $_GET['book_id'] ?? 0;

$book_stmt = $pdo->prepare("SELECT b.*, g.name as genre_name FROM books b LEFT JOIN genres g ON b.genre_id = g.id WHERE b.id = ?");
$book_stmt->execute([$book_id]);
$book = $book_stmt->fetch();

if (!$book) {
    $_SESSION['msg'] = "Книга не найдена";
    header('Location: index.php?page=books');
    exit();
}
?>
<h1>История перемещений книги: <?= htmlspecialchars($book['title']) ?></h1>
<p class="text-muted">Автор: <?= htmlspecialchars($book['author']) ?></p>

<?php
$history_stmt = $pdo->prepare("
    SELECT h.*, r.full_name as reader_name,
           l_from.name as from_location,
           l_to.name as to_location
    FROM history h
    LEFT JOIN readers r ON r.id = h.reader_id
    LEFT JOIN locations l_from ON l_from.id = h.from_location_id
    LEFT JOIN locations l_to ON l_to.id = h.to_location_id
    WHERE h.book_id = ?
    ORDER BY h.movement_date DESC
");
$history_stmt->execute([$book_id]);
$history = $history_stmt->fetchAll();

if (count($history) == 0):
?>
    <div class="alert alert-info">История перемещений отсутствует</div>
<?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Дата</th>
                <th>Читатель</th>
                <th>Действие</th>
                <th>Откуда</th>
                <th>Куда</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $h): ?>
            <tr>
                <td><?= date('d.m.Y H:i', strtotime($h['movement_date'])) ?></td>
                <td><?= htmlspecialchars($h['reader_name']) ?></td>
                <td>
                    <?php if ($h['action_type'] == 'получил'): ?>
                        <span class="badge bg-success">Взял</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Вернул</span>
                    <?php endif; ?>
                </td>
                <td><?= $h['from_location'] ?: '-' ?></td>
                <td><?= $h['to_location'] ?: '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="index.php?page=books" class="btn btn-secondary mt-3">Назад к книгам</a>