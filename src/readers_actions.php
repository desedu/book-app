<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_POST['add_reader'])) {
    $password = md5($_POST['password']);
    $favorite_genres = isset($_POST['favorite_genres']) ? implode(',', $_POST['favorite_genres']) : '';
    $stmt = $pdo->prepare("INSERT INTO readers (username, full_name, email, favorite_genres, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['username'], $_POST['full_name'], $_POST['email'], $favorite_genres, $password]);
}

if (isset($_GET['delete_reader'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE reader_id = ?");
    $stmt->execute([$_GET['delete_reader']]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("DELETE FROM readers WHERE id = ?");
        $stmt->execute([$_GET['delete_reader']]);
    }
}

header('Location: ../index.php');
exit;