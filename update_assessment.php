<?php
session_start();
require 'db_connection.php';

// Ensure the browser expects a JSON response
header('Content-Type: application/json');

// 1. SECURITY CHECK
if (!isset($_SESSION['teacher_authorized']) || $_SESSION['teacher_authorized'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. DATA EXTRACTION
    // This logic detects if the data is coming from the "Award" button OR the "Auto-save" onchange events
    $p_id = $_POST['project_id'] ?? null;

    if (!$p_id) {
        echo json_encode(['status' => 'error', 'message' => 'Missing Project ID.']);
        exit;
    }

    // Determine values: check for direct keys first (Award button), 
    // then check for 'field/value' pairs (Auto-save)
    $level   = $_POST['level']   ?? ($_POST['field'] == 'level'   ? $_POST['value'] : null);
    $marks   = $_POST['marks']   ?? ($_POST['field'] == 'marks'   ? $_POST['value'] : null);
    $remarks = $_POST['remarks'] ?? ($_POST['field'] == 'remarks' ? $_POST['value'] : null);

    try {
        // 3. DATABASE UPDATE
        // IFNULL(?, column_name) keeps the old value if the new one (?) is null
        $sql = "UPDATE projects SET 
                cbc_level = IFNULL(?, cbc_level), 
                marks     = IFNULL(?, marks), 
                remarks   = IFNULL(?, remarks) 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$level, $marks, $remarks, $p_id])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database failed to update row.']);
        }

    } catch (Exception $e) {
        // Captures SQL errors (like missing columns) and sends them to the JS console
        echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}