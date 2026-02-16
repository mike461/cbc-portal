<?php
session_start();
require 'db_connection.php';

// Security: Only allow authorized teachers to perform a reset
if (!isset($_SESSION['teacher_authorized'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    
    // Default password to be given to the student
    $default_password = 'password123'; 
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

    try {
        $sql = "UPDATE students SET password = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$hashed_password, $student_id])) {
            echo json_encode(['status' => 'success', 'new_pass' => $default_password]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update database.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>