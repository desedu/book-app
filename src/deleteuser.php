<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    if ($user_id == $_SESSION['id']) {
        $_SESSION['msg'] = "Вы не можете удалить свою учетную запись";
        header('Location: ../index.php?page=users');
        exit();
    }
    
    try {
        $reviews_stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE reader_id = ?");
        $reviews_stmt->execute([$user_id]);
        $reviews_count = $reviews_stmt->fetchColumn();
        
        $books_stmt = $pdo->prepare("
            SELECT COUNT(*) FROM books b 
            WHERE b.id IN (
                SELECT book_id FROM history h 
                WHERE h.reader_id = ? AND h.action_type = 'получил'
                AND h.id NOT IN (
                    SELECT previous_movement_id FROM history 
                    WHERE previous_movement_id IS NOT NULL
                )
            )
        ");
        $books_stmt->execute([$user_id]);
        $books_count = $books_stmt->fetchColumn();
        
        if ($reviews_count > 0 || $books_count > 0) {
            $_SESSION['msg'] = "Нельзя удалить пользователя, у которого есть отзывы ($reviews_count) или книги ($books_count)";
            header('Location: ../index.php?page=users');
            exit();
        }
        
        $stmt = $pdo->prepare("DELETE FROM readers WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $_SESSION['msg'] = "Пользователь успешно удален";
        
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Ошибка удаления пользователя: " . $e->getMessage();
    }
}

header('Location: ../index.php?page=users');
exit();
?>