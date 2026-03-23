<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if (empty($book_id) || empty($rating) || empty($comment)) {
        $_SESSION['msg'] = "Все поля обязательны для заполнения";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (book_id, reader_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$book_id, $_SESSION['id'], $rating, $comment]);
            $_SESSION['msg'] = "Отзыв успешно добавлен";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Ошибка добавления отзыва: " . $e->getMessage();
        }
    }
}

header('Location: ../index.php?page=reviews');
exit();
?>