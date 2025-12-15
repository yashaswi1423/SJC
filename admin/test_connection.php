<?php
// Simple database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic PHP
    echo "✅ PHP is working<br>";
    
    // Test file paths
    $configPath = '../config/database_config.php';
    if (file_exists($configPath)) {
        echo "✅ Config file found<br>";
        require_once $configPath;
    } else {
        echo "❌ Config file not found at: " . realpath($configPath) . "<br>";
        exit;
    }
    
    // Test database connection
    echo "🔄 Testing database connection...<br>";
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br>";
    
    // Test database and tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Found " . count($tables) . " tables: " . implode(', ', $tables) . "<br>";
    
    // Test problem_statements table
    if (in_array('problem_statements', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM problem_statements");
        $count = $stmt->fetch()['count'];
        echo "✅ problem_statements table has {$count} records<br>";
    } else {
        echo "❌ problem_statements table not found<br>";
    }
    
    echo "<br><strong>✅ All tests passed! Database is ready.</strong>";
    echo "<br><a href='database_dashboard.php'>Go to Dashboard</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br><strong>Troubleshooting:</strong><br>";
    echo "1. Make sure XAMPP/MySQL is running<br>";
    echo "2. Check if database 'smart_horizon_hackathon' exists<br>";
    echo "3. Verify MySQL password in config/password_config.php<br>";
    echo "4. Run database_setup.sql to create tables<br>";
}
?>