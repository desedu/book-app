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
        SELECT h.id, h.from_location_id 
        FROM history h
        WHERE h.book_id = ? 
        AND h.reader_id = ? 
        AND h.action_type = 'получил'
        AND h.id NOT IN (
            SELECT previous_movement_id FROM history 
            WHERE previous_movement_id IS NOT NULL AND action_type = 'вернул'
        )
        ORDER BY h.movement_date DESC LIMIT 1
    ");
    $check_stmt->execute([$book_id, $_SESSION['id']]);
    $take_record = $check_stmt->fetch();
    
    if (!$take_record) {
        $_SESSION['msg'] = 'У вас нет этой книги';
        header('Location: ../index.php?page=mybooks');
        exit();
    }
    
    $loc_stmt = $pdo->prepare("SELECT id FROM locations ORDER BY id LIMIT 1");
    $loc_stmt->execute();
    $default_location = $loc_stmt->fetch();
    
    $insert_stmt = $pdo->prepare("
        INSERT INTO history (book_id, reader_id, to_location_id, previous_movement_id, action_type) 
        VALUES (?, ?, ?, ?, 'вернул')
    ");
    $insert_stmt->execute([$book_id, $_SESSION['id'], $default_location['id'], $take_record['id']]);
    
    $_SESSION['msg'] = 'Книга успешно возвращена!';
    
} catch (PDOException $e) {
    $_SESSION['msg'] = 'Ошибка: ' . $e->getMessage();
}

header('Location: ../index.php?page=books');
exit();
?>