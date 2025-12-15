<?php
// SQLite Database configuration (no server required)
define('DB_FILE', __DIR__ . '/../database/hackathon.db');
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
        // Create database directory if it doesn't exist
        $dbDir = dirname(DB_FILE);
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Create tables if they don't exist
        createTables($pdo);
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Create tables function
function createTables($pdo) {
    // Create problem_statements table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS problem_statements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            org_name TEXT NOT NULL,
            spoc_name TEXT NOT NULL,
            spoc_contact TEXT NOT NULL,
            contact_email TEXT NOT NULL,
            ps_title TEXT NOT NULL,
            ps_description TEXT NOT NULL,
            domain TEXT,
            dataset_link TEXT,
            logo_filename TEXT NOT NULL,
            logo_original_name TEXT NOT NULL,
            logo_file_size INTEGER,
            submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create supporting_documents table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS supporting_documents (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ps_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            original_name TEXT NOT NULL,
            file_size INTEGER,
            file_type TEXT,
            upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ps_id) REFERENCES problem_statements(id) ON DELETE CASCADE
        )
    ");
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