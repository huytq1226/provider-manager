<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này.";
    header("Location: index.php");
    exit;
}

// Read SQL file content
$sqlFile = 'add_notifications.sql';

if (!file_exists($sqlFile)) {
    die("File SQL không tồn tại: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Split SQL by delimiter
$delimiter = ';';
$procedures = [];
$events = [];

// Extract procedures and events (they have different delimiter)
if (preg_match_all('/DELIMITER \/\/(.+?)DELIMITER ;/s', $sql, $matches)) {
    foreach ($matches[1] as $match) {
        // Check if it's a procedure or event
        if (strpos($match, 'CREATE PROCEDURE') !== false) {
            $procedures[] = trim($match);
        } else if (strpos($match, 'CREATE EVENT') !== false) {
            $events[] = trim($match);
        }
    }
    // Remove procedure sections from main SQL
    $sql = preg_replace('/DELIMITER \/\/(.+?)DELIMITER ;/s', '', $sql);
}

// Split by semicolon
$queries = array_filter(array_map('trim', explode($delimiter, $sql)));

// Start transaction
$conn->beginTransaction();

try {
    echo "<h1>Thiết lập Hệ thống Thông báo</h1>";
    
    // Execute each SQL statement
    foreach ($queries as $query) {
        if (!empty($query)) {
            $stmt = $conn->prepare($query);
            $stmt->execute();
            echo "<p>Đã thực thi: " . htmlspecialchars(substr($query, 0, 100)) . "...</p>";
        }
    }
    
    // Execute procedures
    foreach ($procedures as $procedure) {
        if (!empty($procedure)) {
            $stmt = $conn->prepare($procedure);
            $stmt->execute();
            echo "<p>Đã thực thi thủ tục: " . htmlspecialchars(substr($procedure, 0, 100)) . "...</p>";
        }
    }
    
    // Execute events
    foreach ($events as $event) {
        if (!empty($event)) {
            $stmt = $conn->prepare($event);
            $stmt->execute();
            echo "<p>Đã thực thi sự kiện: " . htmlspecialchars(substr($event, 0, 100)) . "...</p>";
        }
    }
    
    // Enable event scheduler
    $conn->exec("SET GLOBAL event_scheduler = ON");
    echo "<p>Đã bật Event Scheduler.</p>";
    
    // Generate initial notifications
    $conn->query("CALL GenerateBillDueNotifications()");
    $conn->query("CALL GenerateContractExpiringNotifications()");
    echo "<p>Đã tạo thông báo ban đầu.</p>";
    
    // Commit transaction
    $conn->commit();
    
    echo "<div class='alert alert-success'>Hệ thống thông báo đã được thiết lập thành công!</div>";
    echo "<p><a href='index.php' class='btn btn-primary'>Về trang chủ</a></p>";
} catch (PDOException $e) {
    // Rollback on error
    $conn->rollBack();
    echo "<div class='alert alert-danger'>Lỗi: " . $e->getMessage() . "</div>";
    echo "<p><a href='index.php' class='btn btn-primary'>Về trang chủ</a></p>";
}
?> 