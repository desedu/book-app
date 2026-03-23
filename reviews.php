<?php
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}
?>
<h1>Отзывы о книгах</h1>

<div class="mb-3" id="addReviewForm">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Добавить отзыв</h5>
        </div>
        <div class="card-body">
            <form method="post" action="./src/insertreview.php">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <select name="book_id" class="form-select" required>
                            <option value="">Выберите книгу</option>
                            <?php
                            $books = $pdo->query("SELECT id, title, author FROM books ORDER BY title")->fetchAll();
                            foreach ($books as $book):
                            ?>
                                <option value="<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?> (<?= htmlspecialchars($book['author']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="rating" class="form-select" required>
                            <option value="">Оценка</option>
                            <option value="5">5 звезд</option>
                            <option value="4">4 звезды</option>
                            <option value="3">3 звезды</option>
                            <option value="2">2 звезды</option>
                            <option value="1">1 звезда</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="text" name="comment" class="form-control" placeholder="Ваш комментарий" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-success w-100">Отправить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$stmt = $pdo->prepare("
    SELECT r.*, b.title as book_title, b.author as book_author, 
           rd.full_name as reader_name, rd.username as reader_username
    FROM reviews r
    JOIN books b ON r.book_id = b.id
    JOIN readers rd ON r.reader_id = rd.id
    ORDER BY r.review_date DESC
");
$stmt->execute();
$reviews = $stmt->fetchAll();

foreach ($reviews as $review):
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $review['rating'] ? '★' : '☆';
    }
?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5><?= htmlspecialchars($review['book_title']) ?></h5>
                    <p class="text-muted mb-1">Автор: <?= htmlspecialchars($review['book_author']) ?></p>
                    <div class="mb-2">
                        <span class="text-warning fs-5"><?= $stars ?></span>
                        <span class="ms-2 text-muted">(<?= $review['rating'] ?>/5)</span>
                    </div>
                    <p class="mb-1"><?= htmlspecialchars($review['comment']) ?></p>
                    <small class="text-muted">
                        <?= htmlspecialchars($review['reader_name']) ?> (@<?= htmlspecialchars($review['reader_username']) ?>)
                        • <?= date('d.m.Y H:i', strtotime($review['review_date'])) ?>
                    </small>
                </div>
                <?php if ($_SESSION['id'] == $review['reader_id']): ?>
                    <a href="./src/deletereview.php?id=<?= $review['id'] ?>" class="btn btn-sm btn-danger">Удалить</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if (count($reviews) == 0): ?>
    <div class="alert alert-info">Пока нет отзывов. Будьте первым!</div>
<?php endif; ?>