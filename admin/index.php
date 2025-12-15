<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Smart Horizon Hackathon</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            background: white; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        h1 { 
            color: #2b2d73; 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .admin-links { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-top: 30px; 
        }
        .admin-card { 
            background: #f8f9fa; 
            padding: 25px; 
            border-radius: 8px; 
            text-align: center; 
            border: 2px solid #e9ecef; 
            transition: all 0.3s; 
        }
        .admin-card:hover { 
            border-color: #2b2d73; 
            transform: translateY(-2px); 
        }
        .admin-card h3 { 
            color: #2b2d73; 
            margin-bottom: 15px; 
        }
        .admin-card p { 
            color: #666; 
            margin-bottom: 20px; 
        }
        .btn { 
            background: #2b2d73; 
            color: white; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block; 
            transition: background 0.3s; 
        }
        .btn:hover { 
            background: #1f225e; 
        }
        .status-indicator { 
            display: inline-block; 
            width: 10px; 
            height: 10px; 
            border-radius: 50%; 
            margin-right: 8px; 
        }
        .status-online { background: #28a745; }
        .status-offline { background: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ Admin Panel</h1>
        <p style="text-align: center; color: #666;">Smart Horizon Hackathon Management System</p>
        
        <?php
        // Check database connection status
        $dbStatus = false;
        $dbMessage = "";
        try {
            require_once '../config/database_config.php';
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT COUNT(*) FROM problem_statements");
            $count = $stmt->fetch()['count'];
            $dbStatus = true;
            $dbMessage = "Connected - {$count} submissions";
        } catch (Exception $e) {
            $dbMessage = "Connection failed: " . $e->getMessage();
        }
        ?>
        
        <div style="text-align: center; margin: 20px 0; padding: 15px; background: <?= $dbStatus ? '#d4edda' : '#f8d7da' ?>; border-radius: 5px;">
            <span class="status-indicator <?= $dbStatus ? 'status-online' : 'status-offline' ?>"></span>
            <strong>Database Status:</strong> <?= $dbMessage ?>
        </div>
        
        <div class="admin-links">
            <div class="admin-card">
                <h3>🔧 Test Connection</h3>
                <p>Test database connectivity and troubleshoot issues</p>
                <a href="test_connection.php" class="btn">Run Tests</a>
            </div>
            
            <div class="admin-card">
                <h3>📊 Dashboard</h3>
                <p>View statistics and analytics dashboard</p>
                <a href="database_dashboard.php" class="btn">View Dashboard</a>
            </div>
            
            <div class="admin-card">
                <h3>📋 Submissions</h3>
                <p>Manage and review problem statement submissions</p>
                <a href="view_submissions.php" class="btn">Manage Submissions</a>
            </div>
            
            <div class="admin-card">
                <h3>🗄️ Database Browser</h3>
                <p>Browse and query database tables directly</p>
                <a href="database_browser.php" class="btn">Browse Database</a>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="../index.html" style="color: #666; text-decoration: none;">← Back to Home</a>
        </div>
    </div>
</body>
</html>