<?php
session_start();
require 'db_connection.php';

// Security: Only authorized teachers can delete
if (!isset($_SESSION['teacher_authorized'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];

    try {
        // 1. Get the file path first so we can delete the actual file
        $stmt = $pdo->prepare("SELECT media_url FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch();

        if ($project) {
            $file_path = $project['media_url'];

            // 2. Delete the record from the database
            // (Assessments will be deleted automatically if you used 'ON DELETE CASCADE' in your SQL)
            $delete_stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            
            if ($delete_stmt->execute([$project_id])) {
                // 3. Delete the physical file from the 'uploads' folder
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                echo json_encode(['status' => 'success']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>