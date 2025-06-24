# Database Setup Instructions

This document provides instructions on how to set up the database for this application on different computers.

## Option 1: Using setup_database.php (Recommended)

The easiest way to set up the database is using the `setup_database.php` script:

1. Make sure you have a MySQL/MariaDB server running (like XAMPP, WAMP, or standalone MySQL)
2. Open the `setup_database.php` file and modify these settings to match your environment:
   ```php
   $host = "localhost";
   $port = "3307";  // Usually 3306 for default MySQL installations
   $username = "root";
   $password = "";  // Enter your database password here
   ```
3. Access the script in your browser:
   ```
   http://localhost/path-to-your-project/setup_database.php
   ```
4. The script will create the `provider_management` database and all required tables with sample data

## Option 2: Using phpMyAdmin

If you prefer using phpMyAdmin:

1. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
2. Create a new database named `provider_management`
3. Select the database and go to the "Import" tab
4. Upload the `database.sql` file and click "Go"

## Option 3: Using MySQL Command Line

If you're comfortable with the command line:

1. Open a terminal or command prompt
2. Connect to your MySQL server:
   ```
   mysql -u root -p -h localhost -P 3307
   ```
   (Replace with your username, host, and port as needed)
3. Run the SQL script:
   ```
   source path/to/database.sql
   ```
   Or from outside MySQL:
   ```
   mysql -u root -p -h localhost -P 3307 < database.sql
   ```

## Configuration Update

After setting up the database, make sure to update the `config/database.php` file if your database credentials differ:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // Use your MySQL port
define('DB_USER', 'root');  // Your username
define('DB_PASS', '');      // Your password
define('DB_NAME', 'provider_management');
```
