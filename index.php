<?php
// Simple diagnostic page to check if PHP is working
?>
<!DOCTYPE html>
<html>
<head>
    <title>XAMPP Diagnostic - Smart Horizon Hackathon</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #e3f2fd; color: #0d47a1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <h1>🚀 XAMPP Diagnostic - Smart Horizon Hackathon</h1>
    
    <div class="success">
        <h2>✅ PHP is Working!</h2>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p><strong>Current Directory:</strong> <?php echo __DIR__; ?></p>
    </div>

    <div class="info">
        <h3>📁 File Check</h3>
        <?php
        $files = [
            'upload-ps.html' => 'Submission Form',
            'admin_access.html' => 'Admin Access',
            'xampp_test.php' => 'System Test',
            'api/submit_problem_statement.php' => 'Submission API',
            'admin/view_submissions.php' => 'Admin Panel'
        ];
        
        foreach ($files as $file => $description) {
            if (file_exists($file)) {
                echo "<p>✅ <strong>$description:</strong> $file (exists)</p>";
            } else {
                echo "<p>❌ <strong>$description:</strong> $file (missing)</p>";
            }
        }
        ?>
    </div>

    <div class="info">
        <h3>🔗 Quick Links</h3>
        <a href="upload-ps.html" class="btn">📝 Submission Form</a>
        <a href="admin_access.html" class="btn">👨‍💼 Admin Access</a>
        <a href="xampp_test.php" class="btn">🧪 System Test</a>
        <a href="test_xampp_connection.php" class="btn">🔗 Connection Test</a>
    </div>

    <div class="info">
        <h3>📋 Setup Status</h3>
        <p><strong>Current URL:</strong> <?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></p>
        <p><strong>Expected URLs:</strong></p>
        <ul>
            <li>Main: http://localhost/SJC/index.html</li>
            <li>Submit: http://localhost/SJC/upload-ps.html</li>
            <li>Admin: http://localhost/SJC/admin/view_submissions.php</li>
        </ul>
    </div>

    <?php if (!file_exists('config/database_config.php')): ?>
    <div class="error">
        <h3>⚠️ Configuration Missing</h3>
        <p>Database configuration files are missing. Please ensure all files are copied correctly.</p>
    </div>
    <?php endif; ?>

</body>
</html>