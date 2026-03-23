<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

if (strlen($_POST['title']) >= 2) {
    $cover_image = null;
    
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = 'book_' . uniqid() . '.' . $ext;
        $filepath = 'uploads/' . $filename;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $filepath)) {
            $cover_image = $filepath;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        $sql = 'INSERT INTO books(title, author, genre_id, condition_book, cover_image) 
                VALUES(?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['title'], 
            $_POST['author'], 
            $_POST['genre_id'] ?: null, 
            $_POST['condition_book'], 
            $cover_image
        ]);
        
        $book_id = $pdo->lastInsertId();
        
        $history_stmt = $pdo->prepare("
            INSERT INTO history(book_id, reader_id, to_location_id, action_type) 
            VALUES(?, ?, ?, 'вернул')
        ");
        $history_stmt->execute([$book_id, $_SESSION['id'], $_POST['location_id']]);
        
        $pdo->commit();
        $_SESSION['msg'] = "Книга успешно добавлена";
        
    } catch (PDOException $error) {
        $pdo->rollBack();
        $_SESSION['msg'] = "Ошибка: " . $error->getMessage();
    }
} else {
    $_SESSION['msg'] = "Название книги должно содержать не менее 2 символов";
}

header('Location: ../index.php?page=books');
exit();
?>