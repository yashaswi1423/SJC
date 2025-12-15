<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'smart_horizon_hackathon');
define('DB_USER', 'root');     // XAMPP default username
// Include password configuration
require_once __DIR__ . '/password_config.php';
define('DB_PASS', MYSQL_PASSWORD);         // Password from password_config.php

// File upload configuration
define('UPLOAD_DIR', '../uploads/');
define('LOGO_DIR', '../uploads/logos/');
define('DOCS_DIR', '../uploads/documents/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('MAX_LOGO_SIZE', 5 * 1024 * 1024);  // 5MB

// Allowed file types
define('ALLOWED_LOGO_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation']);

define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Database connection function
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,  // Use persistent connections
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}

// Create upload directories if they don't exist
function createUploadDirectories() {
    $directories = [UPLOAD_DIR, LOGO_DIR, DOCS_DIR];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Initialize upload directories
createUploadDirectories();
?>