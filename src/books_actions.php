<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_POST['add_book'])) {
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/covers/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = 'book_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $filepath)) {
            $cover_image = 'uploads/covers/' . $filename;
        }
    }
    
    $main_genre_id = $_POST['main_genre_id'] ?: null;
    
    $stmt = $pdo->prepare("INSERT INTO books (title, author, genre_id, condition_book, cover_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['title'], $_POST['author'], $main_genre_id, $_POST['condition_book'], $cover_image]);
}

if (isset($_GET['delete_book'])) {
    $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
    $stmt->execute([$_GET['delete_book']]);
    $book = $stmt->fetch();
    if ($book && $book['cover_image'] && file_exists('../' . $book['cover_image'])) {
        unlink('../' . $book['cover_image']);
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM history WHERE book_id = ?");
    $stmt->execute([$_GET['delete_book']]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$_GET['delete_book']]);
    }
}

header('Location: ../index.php');
exit;