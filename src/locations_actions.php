<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_POST['add_location'])) {
    $stmt = $pdo->prepare("INSERT INTO locations (name, address) VALUES (?, ?)");
    $stmt->execute([$_POST['name'], $_POST['address']]);
}

if (isset($_GET['delete_location'])) {
    $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->execute([$_GET['delete_location']]);
}

header('Location: ../index.php');
exit;