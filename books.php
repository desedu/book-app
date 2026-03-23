<?php
$locations = $pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();

$selected_location = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;

$taken_books = [];
$taken_stmt = $pdo->query("
    SELECT h.book_id, r.full_name as taken_by, r.username as taken_by_username
    FROM history h
    JOIN readers r ON r.id = h.reader_id
    WHERE h.action_type = 'получил'
    AND h.id NOT IN (
        SELECT previous_movement_id FROM history 
        WHERE previous_movement_id IS NOT NULL AND action_type = 'вернул'
    )
");
while ($row = $taken_stmt->fetch()) {
    $taken_books[$row['book_id']] = [
        'name' => $row['taken_by'],
        'username' => $row['taken_by_username']
    ];
}

$sql = "
    SELECT b.*, g.name as genre_name,
           (
               SELECT to_location_id FROM history 
               WHERE book_id = b.id AND action_type = 'вернул' 
               ORDER BY movement_date DESC LIMIT 1
           ) as last_location_id
    FROM books b 
    LEFT JOIN genres g ON b.genre_id = g.id
";

if ($selected_location > 0) {
    $sql .= " WHERE b.id IN (
        SELECT book_id FROM history 
        WHERE to_location_id = ? AND action_type = 'вернул'
        AND movement_date = (
            SELECT MAX(movement_date) FROM history h2 
            WHERE h2.book_id = history.book_id AND h2.action_type = 'вернул'
        )
    )";
}

$sql .= " ORDER BY b.title ASC";

$stmt = $pdo->prepare($sql);
if ($selected_location > 0) {
    $stmt->execute([$selected_location]);
} else {
    $stmt->execute();
}
$books = $stmt->fetchAll();
?>
<h1>Все книги</h1>


<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Добавить новую книгу</h5>
    </div>
    <div class="card-body">
        <form method="post" action="./src/insertbook.php" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="title" class="form-control" placeholder="Название" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="author" class="form-control" placeholder="Автор" required>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="genre_id" class="form-select">
                        <option value="">Жанр</option>
                        <?php
                        $genres = $pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();
                        foreach ($genres as $genre):
                        ?>
                            <option value="<?= $genre['id'] ?>"><?= htmlspecialchars($genre['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="condition_book" class="form-select">
                        <option value="отличное">Отличное</option>
                        <option value="хорошее" selected>Хорошее</option>
                        <option value="удовлетворительное">Удовлетворительное</option>
                        <option value="плохое">Плохое</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="location_id" class="form-select" required>
                        <option value="">Локация</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 mb-2">
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-12">
                    <button type="submit" name="add_book" class="btn btn-success">Добавить книгу</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="books">
            <div class="col-md-10">
                <label class="form-label">Фильтр по локации</label>
                <select name="location_id" class="form-select">
                    <option value="0">Все локации</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['id'] ?>" <?= $selected_location == $location['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($location['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Применить</button>
            </div>
        </form>
    </div>
</div>

<?php if (count($books) == 0): ?>
    <div class="alert alert-info">Книги не найдены</div>
<?php else: ?>
    <?php foreach ($books as $book): 
        $is_taken = isset($taken_books[$book['id']]);
        $taken_by = $is_taken ? $taken_books[$book['id']]['name'] : '';
        $taken_by_username = $is_taken ? $taken_books[$book['id']]['username'] : '';
        
        $location_name = 'Неизвестно';
        if ($book['last_location_id']) {
            $loc_stmt = $pdo->prepare("SELECT name FROM locations WHERE id = ?");
            $loc_stmt->execute([$book['last_location_id']]);
            $location = $loc_stmt->fetch();
            $location_name = $location ? $location['name'] : 'Неизвестно';
        }
        
        $card_class = $is_taken ? 'border-secondary bg-light' : '';
        
        $delete_url = "./src/deletebook.php?id=" . $book['id'];

    ?>
        <div class="card mb-3 <?= $card_class ?>">
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
                        <h5>
                            <?= htmlspecialchars($book['title']) ?>
                            <?php if ($is_taken): ?>
                                <span class="badge bg-warning text-dark">Занята</span>
                            <?php else: ?>
                                <span class="badge bg-success">Свободна</span>
                            <?php endif; ?>
                        </h5>
                        <p class="mb-1">Автор: <?= htmlspecialchars($book['author']) ?></p>
                        <p class="mb-1">Жанр: <?= htmlspecialchars($book['genre_name'] ?? '-') ?></p>
                        <p class="mb-1">Состояние: <?= htmlspecialchars($book['condition_book']) ?></p>
                        <p class="mb-1">Локация: <?= htmlspecialchars($location_name) ?></p>
                        <?php if ($is_taken): ?>
                            <p class="mb-1 text-danger">Взял: <?= htmlspecialchars($taken_by) ?> (@<?= htmlspecialchars($taken_by_username) ?>)</p>
                        <?php endif; ?>
                        <a href="index.php?page=history&book_id=<?= $book['id'] ?>" class="btn btn-sm btn-info">История</a>
                        <a href="./src/editbook.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>
                    </div>
                    <div class="col-md-3 text-end">
                        <?php if (!$is_taken): ?>
                            <a href="./src/takebook.php?book_id=<?= $book['id'] ?>" class="btn btn-success">Взять книгу</a>
                            <br>
                            <a href="<?= $delete_url ?>" class="btn btn-danger mt-2" onclick="<?= $delete_confirm ?>">Удалить</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Недоступна</button>
                            <br>
                            <a href="<?= $delete_url ?>" class="btn btn-danger mt-2" onclick="<?= $delete_confirm ?>">Удалить</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
