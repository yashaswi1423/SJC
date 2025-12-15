<?php
// XAMPP MySQL Setup and Test Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 XAMPP MySQL Setup for Smart Horizon Hackathon</h2>";

// Test different password configurations
$passwords = ['', 'root', 'password', 'admin'];
$workingPassword = null;

echo "<h3>1. Testing MySQL Connection...</h3>";

foreach ($passwords as $pwd) {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;charset=utf8mb4",
            'root',
            $pwd,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✅ Connection successful with password: '" . ($pwd ?: 'empty') . "'<br>";
        $workingPassword = $pwd;
        break;
    } catch (PDOException $e) {
        echo "❌ Failed with password '" . ($pwd ?: 'empty') . "': " . $e->getMessage() . "<br>";
    }
}

if ($workingPassword === null) {
    echo "<p style='color: red;'>❌ Could not connect to MySQL. Please check XAMPP MySQL service.</p>";
    exit;
}

// Update password config file
echo "<h3>2. Updating Password Configuration...</h3>";
$configContent = "<?php\n// MySQL Password Configuration - Auto-updated\ndefine('MYSQL_PASSWORD', '$workingPassword');\n?>";
file_put_contents('config/password_config.php', $configContent);
echo "✅ Password configuration updated<br>";

// Create database and tables
echo "<h3>3. Setting up Database...</h3>";

try {
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS smart_horizon_hackathon");
    echo "✅ Database 'smart_horizon_hackathon' created/verified<br>";
    
    // Use the database
    $pdo->exec("USE smart_horizon_hackathon");
    
    // Create problem_statements table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS problem_statements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            org_name VARCHAR(255) NOT NULL,
            spoc_name VARCHAR(255) NOT NULL,
            spoc_contact VARCHAR(20) NOT NULL,
            contact_email VARCHAR(255) NOT NULL,
            ps_title VARCHAR(500) NOT NULL,
            ps_description TEXT NOT NULL,
            domain VARCHAR(100),
            dataset_link VARCHAR(500),
            logo_filename VARCHAR(255) NOT NULL,
            logo_original_name VARCHAR(255) NOT NULL,
            logo_file_size INT,
            submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Table 'problem_statements' created/verified<br>";
    
    // Create supporting_documents table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS supporting_documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ps_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_size INT,
            file_type VARCHAR(100),
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ps_id) REFERENCES problem_statements(id) ON DELETE CASCADE
        )
    ");
    echo "✅ Table 'supporting_documents' created/verified<br>";
    
    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ps_status ON problem_statements(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ps_submission_date ON problem_statements(submission_date)");
    echo "✅ Database indexes created<br>";
    
} catch (PDOException $e) {
    echo "❌ Database setup error: " . $e->getMessage() . "<br>";
}

// Create upload directories
echo "<h3>4. Creating Upload Directories...</h3>";
$directories = ['uploads', 'uploads/logos', 'uploads/documents'];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created directory: $dir<br>";
    } else {
        echo "✅ Directory exists: $dir<br>";
    }
}

// Test the API endpoint
echo "<h3>5. Testing API Configuration...</h3>";
if (file_exists('api/submit_problem_statement.php')) {
    echo "✅ API file exists: api/submit_problem_statement.php<br>";
} else {
    echo "❌ API file missing: api/submit_problem_statement.php<br>";
}

// Check admin files
echo "<h3>6. Checking Admin Files...</h3>";
$adminFiles = [
    'admin/view_submissions.php',
    'admin/database_dashboard.php',
    'admin/database_browser.php'
];

foreach ($adminFiles as $file) {
    if (file_exists($file)) {
        echo "✅ Admin file exists: $file<br>";
    } else {
        echo "❌ Admin file missing: $file<br>";
    }
}

echo "<h3>✅ Setup Complete!</h3>";
echo "<p><strong>Your XAMPP system is now configured for the hackathon submission system.</strong></p>";

echo "<h4>🌐 Access Points:</h4>";
echo "<ul>";
echo "<li><a href='index.html' target='_blank'>Main Website</a></li>";
echo "<li><a href='upload-ps.html' target='_blank'>Submit Problem Statement (Node.js)</a></li>";
echo "<li><a href='upload-ps-php.html' target='_blank'>Submit Problem Statement (PHP)</a> - <em>Create this file</em></li>";
echo "<li><a href='admin/view_submissions.php' target='_blank'>Admin Panel (PHP)</a></li>";
echo "<li><a href='admin_panel.html' target='_blank'>Admin Panel (Node.js)</a></li>";
echo "</ul>";

echo "<h4>📊 Database Info:</h4>";
echo "<ul>";
echo "<li><strong>Host:</strong> localhost</li>";
echo "<li><strong>Database:</strong> smart_horizon_hackathon</li>";
echo "<li><strong>Username:</strong> root</li>";
echo "<li><strong>Password:</strong> '$workingPassword'</li>";
echo "</ul>";

// Show current submissions count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM problem_statements");
    $count = $stmt->fetch()['count'];
    echo "<p><strong>Current submissions in database:</strong> $count</p>";
} catch (Exception $e) {
    echo "<p>Could not count submissions: " . $e->getMessage() . "</p>";
}
?>