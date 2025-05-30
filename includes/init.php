<?php
/**
 * Initialization file for the application
 * Include this file at the beginning of each page to ensure all dependencies are loaded
 */

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Include helper functions
require_once __DIR__ . '/functions.php';

// Include authentication functions
require_once __DIR__ . '/auth.php'; 