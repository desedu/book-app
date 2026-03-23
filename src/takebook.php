<?php
session_start();
require "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

$book_id = (int)$_GET['book_id'];

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
        $_SESSION['msg'] = 'Книга уже кем-то взята';
        header('Location: ../index.php?page=books');
        exit();
    }
    
    $loc_stmt = $pdo->prepare("
        SELECT to_location_id FROM history 
        WHERE book_id = ? AND action_type = 'вернул'
        ORDER BY movement_date DESC LIMIT 1
    ");
    $loc_stmt->execute([$book_id]);
    $last_location = $loc_stmt->fetch();
    
    $from_location_id = $last_location ? $last_location['to_location_id'] : null;
    
    $insert_stmt = $pdo->prepare("
        INSERT INTO history (book_id, reader_id, from_location_id, action_type) 
        VALUES (?, ?, ?, 'получил')
    ");
    $insert_stmt->execute([$book_id, $_SESSION['id'], $from_location_id]);
    
    $_SESSION['msg'] = 'Книга успешно взята!';
    
} catch (PDOException $e) {
    $_SESSION['msg'] = 'Ошибка: ' . $e->getMessage();
}

header('Location: ../index.php?page=mybooks');
exit();
?>