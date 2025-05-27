<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // Use the correct port
define('DB_USER', 'root');  // Mặc định của XAMPP/Laragon
define('DB_PASS', '');      // Mặc định của XAMPP/Laragon
define('DB_NAME', 'provider_management');

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

    // Thiết lập chế độ báo lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Thiết lập charset
    $conn->exec("set names utf8");
} catch(PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
    die();
}
?> 