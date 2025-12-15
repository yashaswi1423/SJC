<?php
echo "<h1>Test Access - Working!</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Current file: " . __FILE__ . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test database connection
echo "<h2>Database Test</h2>";
try {
    require_once '../config/database_config.php';
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM problem_statements");
    $result = $stmt->fetch();
    echo "<p>Total submissions in database: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>File System Test</h2>";
echo "<p>Files in admin directory:</p>";
echo "<ul>";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li>$file</li>";
    }
}
echo "</ul>";

echo "<h2>Navigation Links</h2>";
echo "<p><a href='view_submissions.php'>View Submissions</a></p>";
echo "<p><a href='database_dashboard.php'>Database Dashboard</a></p>";
echo "<p><a href='../index.html'>Home</a></p>";
?>