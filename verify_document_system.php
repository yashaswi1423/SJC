<?php
require_once 'config/database_config.php';

echo "<h2>🔍 Document System Verification</h2>";

try {
    $pdo = getDBConnection();
    
    // Check recent submissions with documents
    echo "<h3>📋 Recent Submissions with Document Count:</h3>";
    $stmt = $pdo->query("
        SELECT ps.id, ps.org_name, ps.submission_date, 
               COUNT(sd.id) as doc_count,
               GROUP_CONCAT(sd.original_name SEPARATOR ', ') as document_names
        FROM problem_statements ps 
        LEFT JOIN supporting_documents sd ON ps.id = sd.ps_id 
        GROUP BY ps.id 
        ORDER BY ps.submission_date DESC 
        LIMIT 10
    ");
    $submissions = $stmt->fetchAll();
    
    if (empty($submissions)) {
        echo "<p>❌ No submissions found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 1rem 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Organization</th><th>Submission Date</th><th>Documents</th><th>Document Names</th>";
        echo "</tr>";
        
        foreach ($submissions as $sub) {
            $docCount = $sub['doc_count'];
            $rowColor = $docCount > 0 ? '#e8f5e8' : '#fff5f5';
            
            echo "<tr style='background: {$rowColor};'>";
            echo "<td>#{$sub['id']}</td>";
            echo "<td>" . htmlspecialchars($sub['org_name']) . "</td>";
            echo "<td>" . date('M j, Y g:i A', strtotime($sub['submission_date'])) . "</td>";
            echo "<td><strong>{$docCount}</strong></td>";
            echo "<td>" . htmlspecialchars($sub['document_names'] ?: 'None') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check files in uploads directory
    echo "<h3>📁 Files in uploads/documents/:</h3>";
    $docsDir = 'uploads/documents';
    if (is_dir($docsDir)) {
        $files = array_diff(scandir($docsDir), ['.', '..']);
        if (empty($files)) {
            echo "<p>❌ No files found in uploads/documents/ directory.</p>";
        } else {
            echo "<p>✅ Found " . count($files) . " files:</p>";
            echo "<ul>";
            foreach ($files as $file) {
                $filePath = $docsDir . '/' . $file;
                $size = filesize($filePath);
                $date = date('M j, Y g:i A', filemtime($filePath));
                echo "<li><strong>{$file}</strong> - " . number_format($size) . " bytes - {$date}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p>❌ uploads/documents/ directory does not exist.</p>";
    }
    
    // Test database connection and table structure
    echo "<h3>🗄️ Database Structure Check:</h3>";
    $stmt = $pdo->query("DESCRIBE supporting_documents");
    $columns = $stmt->fetchAll();
    
    echo "<p>✅ supporting_documents table structure:</p>";
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🧪 Quick Actions:</h3>";
echo "<ul>";
echo "<li><a href='upload-ps.html'>📝 Submit New Problem Statement</a></li>";
echo "<li><a href='xampp_admin_dashboard.php'>👨‍💼 Admin Dashboard</a></li>";
echo "<li><a href='test_document_upload.html'>🧪 Test Document Upload</a></li>";
echo "</ul>";
?>