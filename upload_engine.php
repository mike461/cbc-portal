<?php
session_start();
require 'db_connection.php';

// 1. Security Check: Must be logged in as a student
if (!isset($_SESSION['student_id'])) {
    header("Location: learner_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['project_files'])) {
    $student_id = $_SESSION['student_id'];
    $reflection = htmlspecialchars($_POST['reflection']);
    $upload_dir = 'uploads/';

    // Create folder if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES['project_files']['tmp_name'] as $key => $tmp_name) {
        $file_name = time() . '_' . $_FILES['project_files']['name'][$key];
        $file_path = $upload_dir . $file_name;
        $file_type = $_FILES['project_files']['type'][$key];

        // Determine if it's an image or video for the database
        $media_category = (strpos($file_type, 'video') !== false) ? 'video' : 'image';

        if (move_uploaded_file($tmp_name, $file_path)) {
            try {
                // 2. Insert into Database
                $sql = "INSERT INTO projects (student_id, media_url, media_type, reflection, created_at) 
                        VALUES (?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$student_id, $file_path, $media_category, $reflection])) {
                    // 3. REDIRECT WITH SUCCESS FLAG
                    header("Location: student_dashboard.php?success=1");
                    exit();
                }
            } catch (PDOException $e) {
                die("Database Error: " . $e->getMessage());
            }
        } else {
            die("File upload failed. Check folder permissions.");
        }
    }
} else {
    header("Location: student_dashboard.php");
    exit();
}