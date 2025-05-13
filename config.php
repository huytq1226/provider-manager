<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Mặc định của XAMPP là 'root'
define('DB_PASS', '');      // Mặc định của XAMPP là không có mật khẩu
define('DB_NAME', 'provider_management');

// Create connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}
?> 