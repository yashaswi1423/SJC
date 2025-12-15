<?php
// Database Configuration Switcher
// Change this to switch between SQLite and MySQL

// Set to 'mysql' or 'sqlite'
define('DB_TYPE', 'mysql');

// Include the appropriate database configuration
if (DB_TYPE === 'mysql') {
    require_once __DIR__ . '/database.php';
} else {
    require_once __DIR__ . '/database_sqlite.php';
}
?>