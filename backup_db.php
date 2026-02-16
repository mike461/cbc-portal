<?php
require 'db_connection.php';

// 1. Setup the filename
$filename = "backup_" . date("Y-m-d_H-i-s") . ".sql";

// 2. Set headers to force download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');

try {
    // 3. Get all tables
    $tables = array();
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $sql_output = "-- CBC Portal Backup\n";
    $sql_output .= "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";

    // 4. Loop through tables to extract structure and data
    foreach ($tables as $table) {
        // Create Table Structure
        $res = $pdo->query("SHOW CREATE TABLE $table");
        $show_table = $res->fetch(PDO::FETCH_NUM);
        $sql_output .= "\n\n" . $show_table[1] . ";\n\n";

        // Get Data
        $data_res = $pdo->query("SELECT * FROM $table");
        while ($row = $data_res->fetch(PDO::FETCH_NUM)) {
            $sql_output .= "INSERT INTO $table VALUES(";
            $items = array();
            foreach ($row as $item) {
                // Escape special characters for SQL
                if (isset($item)) {
                    $items[] = $pdo->quote($item);
                } else {
                    $items[] = "NULL";
                }
            }
            $sql_output .= implode(",", $items);
            $sql_output .= ");\n";
        }
    }

    // 5. Output the result to the browser for download
    echo $sql_output;
    exit;

} catch (Exception $e) {
    die("Backup Error: " . $e->getMessage());
}
?>