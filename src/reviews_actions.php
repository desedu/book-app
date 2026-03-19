<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_POST['add_review'])) {
    $stmt = $pdo->prepare("INSERT INTO reviews (book_id, reader_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['book_id'], $_POST['reader_id'], $_POST['rating'], $_POST['comment']]);
}

if (isset($_GET['delete_review'])) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$_GET['delete_review']]);
}

header('Location: ../index.php');
exit;