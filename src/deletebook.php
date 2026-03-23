<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    $book_id = (int)$_GET['id'];
    
    try {
        $check_stmt = $pdo->prepare("
            SELECT COUNT(*) FROM history 
            WHERE book_id = ? 
            AND action_type = 'получил'
            AND id NOT IN (
                SELECT previous_movement_id FROM history 
                WHERE previous_movement_id IS NOT NULL AND action_type = 'вернул'
            )
        ");
        $check_stmt->execute([$book_id]);
        $is_taken = $check_stmt->fetchColumn();
        
        if ($is_taken > 0) {
            $_SESSION['msg'] = "Нельзя удалить книгу, которая находится у читателя! Сначала верните книгу.";
            header('Location: ../index.php?page=books');
            exit();
        }
        
        $img_stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
        $img_stmt->execute([$book_id]);
        $book = $img_stmt->fetch();
        
        $pdo->beginTransaction();
        
        $pdo->prepare("DELETE FROM reviews WHERE book_id = ?")->execute([$book_id]);
        
        $history_ids = $pdo->prepare("SELECT id FROM history WHERE book_id = ?");
        $history_ids->execute([$book_id]);
        $ids = $history_ids->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($ids)) {
            $update_stmt = $pdo->prepare("
                UPDATE history SET previous_movement_id = NULL 
                WHERE previous_movement_id IN (
                    SELECT id FROM (SELECT id FROM history WHERE book_id = ?) as tmp
                )
            ");
            $update_stmt->execute([$book_id]);
            
            $pdo->prepare("DELETE FROM history WHERE book_id = ?")->execute([$book_id]);
        }
        
        $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$book_id]);
        
        $pdo->commit();
        
        if ($book && $book['cover_image'] && file_exists($book['cover_image'])) {
            unlink($book['cover_image']);
        }
        
        $_SESSION['msg'] = "Книга успешно удалена";
        
    } catch (PDOException $error) {
        $pdo->rollBack();
        $_SESSION['msg'] = "Ошибка удаления: " . $error->getMessage();
    }
}

header('Location: ../index.php?page=books');
exit();
?>