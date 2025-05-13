<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

try {
    // Thử truy vấn đơn giản
    $stmt = $conn->query("SELECT * FROM Providers LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Kết nối thành công!<br>";
    echo "Dữ liệu mẫu từ bảng Providers:<br>";
    print_r($result);
} catch(PDOException $e) {
    echo "Lỗi truy vấn: " . $e->getMessage();
}

try {
    echo "<br>";
    $role = $_SESSION['role'];
    echo "Role: " . $role;
} catch(PDOException $e) {
    echo "Lỗi truy vấn: " . $e->getMessage();
}
?> 