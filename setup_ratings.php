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
$sqlFile = 'add_rating_tables.sql';

if (!file_exists($sqlFile)) {
    die("File SQL không tồn tại: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Split SQL by delimiter
$delimiter = ';';
$triggers = [];

// Extract triggers (they have different delimiter)
if (preg_match_all('/DELIMITER \/\/(.+?)DELIMITER ;/s', $sql, $matches)) {
    foreach ($matches[1] as $trigger) {
        $triggers[] = trim($trigger);
    }
    // Remove trigger sections from main SQL
    $sql = preg_replace('/DELIMITER \/\/(.+?)DELIMITER ;/s', '', $sql);
}

// Split by semicolon
$queries = array_filter(array_map('trim', explode($delimiter, $sql)));

// Start transaction
$conn->beginTransaction();

try {
    // Execute each SQL statement
    foreach ($queries as $query) {
        if (!empty($query)) {
            $stmt = $conn->prepare($query);
            $stmt->execute();
            echo "Executed: " . substr($query, 0, 50) . "...<br>";
        }
    }
    
    // Execute triggers with different delimiter
    foreach ($triggers as $trigger) {
        if (!empty($trigger)) {
            $stmt = $conn->prepare($trigger);
            $stmt->execute();
            echo "Executed trigger: " . substr($trigger, 0, 50) . "...<br>";
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<p>All SQL statements executed successfully!</p>";
    echo "<p><a href='provider-details.php?id=1' class='btn btn-primary'>Go to Provider Details</a></p>";
} catch (PDOException $e) {
    // Rollback on error
    $conn->rollBack();
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 