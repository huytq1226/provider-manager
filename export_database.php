<?php
// Script to export the current database structure and data to SQL format
// This will create an updated database.sql file

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Export Tool</h1>";

// Include the database connection
require_once 'config/database.php';
// Now $conn is available from the included file

// Function to get CREATE TABLE statement for a table
function getTableStructure($conn, $tableName) {
    $stmt = $conn->prepare("SHOW CREATE TABLE `$tableName`");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['Create Table'] . ";\n\n";
}

// Function to get INSERT statements for a table
function getTableData($conn, $tableName) {
    $stmt = $conn->prepare("SELECT * FROM `$tableName`");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        return "-- No data in table $tableName\n\n";
    }
    
    $insertStatements = "-- Data for table $tableName\n";
    
    // Group the insert statements for better performance
    $columnNames = array_keys($rows[0]);
    $insertStatements .= "INSERT INTO `$tableName` (`" . implode('`, `', $columnNames) . "`) VALUES\n";
    
    $valuesList = [];
    foreach ($rows as $row) {
        $values = [];
        foreach ($row as $value) {
            if ($value === null) {
                $values[] = "NULL";
            } else {
                $values[] = "'" . str_replace("'", "\'", $value) . "'";
            }
        }
        $valuesList[] = "(" . implode(", ", $values) . ")";
    }
    $insertStatements .= implode(",\n", $valuesList) . ";\n\n";
    
    return $insertStatements;
}

try {
    // Get all table names
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        die("<p>No tables found in the database.</p>");
    }
    
    // Start building the SQL output
    $sql = "-- Export from database: " . DB_NAME . "\n";
    $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "-- Create database\n";
    $sql .= "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`;\n";
    $sql .= "USE `" . DB_NAME . "`;\n\n";
    
    // Drop tables if they exist to avoid conflicts
    $sql .= "-- Drop tables if they exist\n";
    $tablesToDrop = array_reverse($tables); // Reverse to handle foreign key constraints
    foreach ($tablesToDrop as $table) {
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
    }
    $sql .= "\n";
    
    // Get structure and data for each table
    $sql .= "-- Create tables\n";
    foreach ($tables as $table) {
        $sql .= getTableStructure($conn, $table);
        $sql .= getTableData($conn, $table);
    }
    
    // Write to file
    $result = file_put_contents('database.sql', $sql);
    
    if ($result !== false) {
        echo "<p style='color:green;font-weight:bold;'>Database structure and data exported successfully to database.sql!</p>";
        echo "<p>The file includes all tables, structures, and data from your current database.</p>";
        echo "<p>File size: " . round($result / 1024, 2) . " KB</p>";
    } else {
        echo "<p style='color:red'>Failed to write to database.sql file. Check file permissions.</p>";
    }
    
} catch(PDOException $e) {
    die("<p style='color:red'>Database export failed: " . $e->getMessage() . "</p>");
}
?>

<hr>
<h3>Preview of generated SQL:</h3>
<pre style="background:#f4f4f4;padding:10px;border:1px solid #ddd;max-height:300px;overflow:auto;">
<?php
    if (isset($sql)) {
        echo htmlspecialchars(substr($sql, 0, 2000)) . "\n...";
        echo "\n(Showing first 2000 characters only)";
    }
?>
</pre>

<p><a href="database.sql" download>Download database.sql</a></p> 