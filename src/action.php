<?php
session_start();
require_once "../dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_POST['take_book'])) {
    $book_id = $_POST['book_id'];
    $location_id = $_POST['location_id'];
    $reader_id = $_SESSION['id'];
    
    try {
        $pdo->beginTransaction();
        
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM history 
            WHERE book_id = ? AND action_type = 'получил' 
            AND (to_location_id IS NULL OR to_location_id = 0)
        ");
        $check->execute([$book_id]);
        
        if ($check->fetchColumn() > 0) {
            throw new Exception('Книга уже кем-то взята');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO history (book_id, reader_id, from_location_id, to_location_id, action_type) 
            VALUES (?, ?, ?, ?, 'получил')
        ");
        $stmt->execute([$book_id, $reader_id, null, $location_id]);
        
        $pdo->commit();
        $_SESSION['msg'] = "Книга успешно взята";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['msg'] = "Ошибка: " . $e->getMessage();
    }
}

if (isset($_POST['return_book'])) {
    $book_id = $_POST['book_id'];
    $location_id = $_POST['location_id'];
    $reader_id = $_SESSION['id'];
    
    try {
        $pdo->beginTransaction();
        
        $check = $pdo->prepare("
            SELECT id, to_location_id FROM history 
            WHERE book_id = ? AND action_type = 'получил' 
            AND (to_location_id IS NULL OR to_location_id = 0)
            ORDER BY movement_date DESC LIMIT 1
        ");
        $check->execute([$book_id]);
        $last_take = $check->fetch();
        
        if (!$last_take) {
            throw new Exception('Книга не найдена в списке взятых');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO history (book_id, reader_id, from_location_id, to_location_id, previous_movement_id, action_type) 
            VALUES (?, ?, ?, ?, ?, 'вернул')
        ");
        $stmt->execute([$book_id, $reader_id, $last_take['to_location_id'], $location_id, $last_take['id']]);
        
        $pdo->commit();
        $_SESSION['msg'] = "Книга успешно возвращена";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['msg'] = "Ошибка: " . $e->getMessage();
    }
}

header('Location: ../index.php');
exit;