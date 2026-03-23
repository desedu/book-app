<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $check_stmt = $pdo->prepare("SELECT id FROM reviews WHERE id = ? AND reader_id = ?");
        $check_stmt->execute([$_GET['id'], $_SESSION['id']]);
        
        if ($check_stmt->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $_SESSION['msg'] = "Отзыв успешно удален";
        } else {
            $_SESSION['msg'] = "У вас нет прав на удаление этого отзыва";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Ошибка удаления отзыва: " . $e->getMessage();
    }
}

header('Location: ../index.php?page=reviews');
exit();
?>