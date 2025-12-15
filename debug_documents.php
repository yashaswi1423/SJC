<?php
require_once 'config/database_config.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>🔍 Debug Supporting Documents</h2>";
    
    // Check if supporting_documents table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'supporting_documents'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table 'supporting_documents' exists<br><br>";
        
        // Check all documents in the table
        $stmt = $pdo->query("SELECT * FROM supporting_documents ORDER BY ps_id, upload_date");
        $documents = $stmt->fetchAll();
        
        if (empty($documents)) {
            echo "❌ No documents found in supporting_documents table<br>";
            echo "This means documents are not being saved when forms are submitted.<br><br>";
        } else {
            echo "✅ Found " . count($documents) . " documents in database:<br><br>";
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>ID</th><th>PS ID</th><th>Filename</th><th>Original Name</th><th>Size</th><th>Upload Date</th>";
            echo "</tr>";
            
            foreach ($documents as $doc) {
                echo "<tr>";
                echo "<td>" . $doc['id'] . "</td>";
                echo "<td>" . $doc['ps_id'] . "</td>";
                echo "<td>" . htmlspecialchars($doc['filename']) . "</td>";
                echo "<td>" . htmlspecialchars($doc['original_name']) . "</td>";
                echo "<td>" . number_format($doc['file_size']) . " bytes</td>";
                echo "<td>" . $doc['upload_date'] . "</td>";
                echo "</tr>";
            }
            echo "</table><br>";
        }
        
        // Check problem_statements table
        $stmt = $pdo->query("SELECT id, org_name FROM problem_statements ORDER BY id");
        $submissions = $stmt->fetchAll();
        
        echo "<h3>📋 Problem Statements:</h3>";
        foreach ($submissions as $sub) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as doc_count FROM supporting_documents WHERE ps_id = ?");
            $stmt->execute([$sub['id']]);
            $count = $stmt->fetch()['doc_count'];
            
            echo "ID #{$sub['id']}: " . htmlspecialchars($sub['org_name']) . " - {$count} documents<br>";
        }
        
    } else {
        echo "❌ Table 'supporting_documents' does not exist<br>";
        echo "Run setup_mysql_xampp.php to create the table.<br>";
    }
    
    // Check uploads directory
    echo "<br><h3>📁 File System Check:</h3>";
    $uploadsDir = 'uploads/documents';
    if (is_dir($uploadsDir)) {
        $files = scandir($uploadsDir);
        $files = array_filter($files, function($file) {
            return $file !== '.' && $file !== '..';
        });
        
        if (empty($files)) {
            echo "❌ No files found in uploads/documents/ directory<br>";
        } else {
            echo "✅ Found " . count($files) . " files in uploads/documents/:<br>";
            foreach ($files as $file) {
                $size = filesize($uploadsDir . '/' . $file);
                echo "- " . htmlspecialchars($file) . " (" . number_format($size) . " bytes)<br>";
            }
        }
    } else {
        echo "❌ uploads/documents/ directory does not exist<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

<p><a href="xampp_admin_dashboard.php">← Back to Admin Dashboard</a></p>