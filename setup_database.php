<?php
// Database setup script
// This script will create and populate the database using the SQL in database.sql

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Setup Script</h1>";

// Database credentials - CHANGE THESE TO MATCH YOUR ENVIRONMENT
$host = "localhost";
$port = "3307";  // Default MySQL port is 3306, but your config uses 3307
$username = "root";
$password = "";  // Enter your database password here

// Connect to MySQL server
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to MySQL server successfully!</p>";
} catch(PDOException $e) {
    die("<p>Connection failed: " . $e->getMessage() . "</p>");
}

// Read SQL file
$sqlFile = file_get_contents('database.sql');
if (!$sqlFile) {
    die("<p>Error: Could not read database.sql file</p>");
}

echo "<p>SQL file loaded successfully</p>";

// Execute SQL statements
try {
    // Set multi_query
    $statements = explode(';', $sqlFile);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p style='color:green;font-weight:bold;'>Database setup completed successfully!</p>";
    echo "<p>The database 'provider_management' has been created with all tables and sample data.</p>";
    echo "<p>You can now access your application.</p>";
    
} catch(PDOException $e) {
    die("<p style='color:red'>Database setup failed: " . $e->getMessage() . "</p>");
}
?> 