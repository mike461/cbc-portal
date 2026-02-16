<?php
// 1. DATABASE CONFIGURATION
// For local XAMPP: use 'localhost', 'root', and ''
// For InfinityFree: Use the 'MySQL Hostname', 'MySQL Username', and 'MySQL Password' from your panel.

$host = 'sql311.infinityfree.com'; // Replace with your ACTUAL MySQL Hostname 
$db   = 'if0_40895236_cbc_portal'; // Replace with your ACTUAL Database Name 
$user = 'if0_40895236';            // Your MySQL Username 
$pass = 'ClJf9yaFtK';   // Your MySQL Password 
$charset = 'utf8mb4';

// 2. DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. PDO OPTIONS FOR HIGH SECURITY
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throws errors if something is wrong
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returns data as easy-to-read arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Uses real prepared statements to block SQL Injection 
];

try {
    // Establish the secure connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, show a clean message
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>