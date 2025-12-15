<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if config file exists
if (!file_exists('../config/database_config.php')) {
    die('Database configuration file not found. Please check your setup.');
}

require_once '../config/database_config.php';

// Simple authentication
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['admin_password']) && $_POST['admin_password'] === 'hackathon2026') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Dashboard - Smart Horizon Hackathon</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 400px; margin: 100px auto; padding: 20px; }
                .login-form { background: #f5f5f5; padding: 30px; border-radius: 10px; }
                input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
                button { background: #2b2d73; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
                button:hover { background: #1f225e; }
            </style>
        </head>
        <body>
            <div class="login-form">
                <h2>Database Dashboard Login</h2>
                <form method="POST">
                    <input type="password" name="admin_password" placeholder="Enter admin password" required>
                    <button type="submit">Login</button>
                </form>
                <p><small>Default password: hackathon2026</small></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

try {
    $pdo = getDBConnection();
    
    // Test if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'problem_statements'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Database tables not found. Please run the database setup script first.");
    }
    
    // Get comprehensive statistics
    $stats = [];
    
    // Basic counts
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM problem_statements");
    $stats['total_submissions'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM problem_statements GROUP BY status");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['pending'] = $statusCounts['pending'] ?? 0;
    $stats['approved'] = $statusCounts['approved'] ?? 0;
    $stats['rejected'] = $statusCounts['rejected'] ?? 0;
    
    // Domain distribution
    $stmt = $pdo->query("SELECT domain, COUNT(*) as count FROM problem_statements WHERE domain IS NOT NULL AND domain != '' GROUP BY domain ORDER BY count DESC");
    $domainStats = $stmt->fetchAll();
    
    // Recent submissions (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM problem_statements WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_submissions'] = $stmt->fetch()['count'];
    
    // Total documents
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM supporting_documents");
    $stats['total_documents'] = $stmt->fetch()['count'];
    
    // Average documents per submission
    $stats['avg_documents'] = $stats['total_submissions'] > 0 ? round($stats['total_documents'] / $stats['total_submissions'], 1) : 0;
    
    // Top organizations by submissions
    $stmt = $pdo->query("SELECT org_name, COUNT(*) as count FROM problem_statements GROUP BY org_name ORDER BY count DESC LIMIT 5");
    $topOrgs = $stmt->fetchAll();
    
    // Recent activity
    $stmt = $pdo->query("SELECT ps.org_name, ps.ps_title, ps.status, ps.submission_date FROM problem_statements ps ORDER BY ps.submission_date DESC LIMIT 10");
    $recentActivity = $stmt->fetchAll();
    
    // Monthly submission trend (last 6 months)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(submission_date, '%Y-%m') as month,
            COUNT(*) as count
        FROM problem_statements 
        WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(submission_date, '%Y-%m')
        ORDER BY month
    ");
    $monthlyTrend = $stmt->fetchAll();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Dashboard - Smart Horizon Hackathon</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        
        .header { background: linear-gradient(135deg, #2b2d73, #c12d6b); color: white; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 2em; }
        .header-nav { margin-top: 10px; }
        .header-nav a { color: white; text-decoration: none; margin-right: 20px; padding: 8px 15px; border-radius: 5px; transition: background 0.3s; }
        .header-nav a:hover { background: rgba(255,255,255,0.2); }
        .logout { float: right; background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 2.5em; font-weight: bold; margin-bottom: 10px; }
        .stat-label { color: #666; font-size: 1.1em; }
        
        .card-primary .stat-number { color: #2b2d73; }
        .card-success .stat-number { color: #28a745; }
        .card-warning .stat-number { color: #ffc107; }
        .card-danger .stat-number { color: #dc3545; }
        .card-info .stat-number { color: #17a2b8; }
        
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px; }
        .chart-container { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .chart-container h3 { margin-bottom: 20px; color: #2b2d73; }
        
        .domain-chart { display: grid; gap: 10px; }
        .domain-bar { display: flex; align-items: center; }
        .domain-label { min-width: 100px; font-size: 0.9em; }
        .domain-progress { flex: 1; height: 25px; background: #e9ecef; border-radius: 12px; overflow: hidden; margin: 0 10px; }
        .domain-fill { height: 100%; background: linear-gradient(90deg, #2b2d73, #c12d6b); transition: width 0.5s; }
        .domain-count { font-weight: bold; color: #2b2d73; }
        
        .activity-list { max-height: 400px; overflow-y: auto; }
        .activity-item { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: between; align-items: center; }
        .activity-item:last-child { border-bottom: none; }
        .activity-org { font-weight: bold; color: #2b2d73; }
        .activity-title { color: #666; font-size: 0.9em; margin: 5px 0; }
        .activity-date { font-size: 0.8em; color: #999; }
        .activity-status { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .full-width { grid-column: 1 / -1; }
        
        .trend-chart { height: 300px; display: flex; align-items: end; justify-content: space-around; padding: 20px 0; }
        .trend-bar { background: linear-gradient(to top, #2b2d73, #c12d6b); border-radius: 5px 5px 0 0; min-width: 40px; margin: 0 5px; position: relative; }
        .trend-label { position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); font-size: 0.8em; color: #666; }
        .trend-value { position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-size: 0.8em; font-weight: bold; color: #2b2d73; }
        
        .quick-actions { display: flex; gap: 15px; margin-top: 20px; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: bold; transition: all 0.3s; }
        .btn-primary { background: #2b2d73; color: white; }
        .btn-primary:hover { background: #1f225e; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Database Dashboard</h1>
        <div class="header-nav">
            <a href="view_submissions.php">📋 View Submissions</a>
            <a href="database_dashboard.php">📊 Dashboard</a>
            <a href="../index.html">🏠 Home</a>
        </div>
        <a href="?logout=1" class="logout">Logout</a>
        <?php if (isset($_GET['logout'])) { session_destroy(); header('Location: database_dashboard.php'); exit; } ?>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stat-card card-primary">
                <div class="stat-number"><?= $stats['total_submissions'] ?></div>
                <div class="stat-label">Total Submissions</div>
            </div>
            <div class="stat-card card-warning">
                <div class="stat-number"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-card card-success">
                <div class="stat-number"><?= $stats['approved'] ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card card-danger">
                <div class="stat-number"><?= $stats['rejected'] ?></div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-card card-info">
                <div class="stat-number"><?= $stats['recent_submissions'] ?></div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="stat-card card-info">
                <div class="stat-number"><?= $stats['total_documents'] ?></div>
                <div class="stat-label">Total Documents</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Domain Distribution -->
            <div class="chart-container">
                <h3>📈 Submissions by Domain</h3>
                <div class="domain-chart">
                    <?php 
                    $maxCount = !empty($domainStats) ? max(array_column($domainStats, 'count')) : 1;
                    foreach ($domainStats as $domain): 
                        $percentage = ($domain['count'] / $maxCount) * 100;
                    ?>
                    <div class="domain-bar">
                        <div class="domain-label"><?= htmlspecialchars($domain['domain'] ?: 'Other') ?></div>
                        <div class="domain-progress">
                            <div class="domain-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <div class="domain-count"><?= $domain['count'] ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($domainStats)): ?>
                    <p style="text-align: center; color: #666; font-style: italic;">No domain data available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="chart-container">
                <h3>🕒 Recent Activity</h3>
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div style="flex: 1;">
                            <div class="activity-org"><?= htmlspecialchars($activity['org_name']) ?></div>
                            <div class="activity-title"><?= htmlspecialchars(substr($activity['ps_title'], 0, 50)) ?>...</div>
                            <div class="activity-date"><?= date('M j, Y H:i', strtotime($activity['submission_date'])) ?></div>
                        </div>
                        <div class="activity-status status-<?= $activity['status'] ?>">
                            <?= ucfirst($activity['status']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="chart-container full-width">
            <h3>📅 Monthly Submission Trend</h3>
            <div class="trend-chart">
                <?php 
                $maxTrendCount = !empty($monthlyTrend) ? max(array_column($monthlyTrend, 'count')) : 1;
                foreach ($monthlyTrend as $month): 
                    $height = ($month['count'] / $maxTrendCount) * 250;
                ?>
                <div class="trend-bar" style="height: <?= $height ?>px;">
                    <div class="trend-label"><?= date('M Y', strtotime($month['month'] . '-01')) ?></div>
                    <div class="trend-value"><?= $month['count'] ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($monthlyTrend)): ?>
                <p style="text-align: center; color: #666; font-style: italic;">No trend data available</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Organizations -->
        <?php if (!empty($topOrgs)): ?>
        <div class="chart-container full-width">
            <h3>🏢 Top Organizations by Submissions</h3>
            <div class="domain-chart">
                <?php 
                $maxOrgCount = max(array_column($topOrgs, 'count'));
                foreach ($topOrgs as $org): 
                    $percentage = ($org['count'] / $maxOrgCount) * 100;
                ?>
                <div class="domain-bar">
                    <div class="domain-label" style="min-width: 200px;"><?= htmlspecialchars($org['org_name']) ?></div>
                    <div class="domain-progress">
                        <div class="domain-fill" style="width: <?= $percentage ?>%"></div>
                    </div>
                    <div class="domain-count"><?= $org['count'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="chart-container full-width">
            <h3>⚡ Quick Actions</h3>
            <div class="quick-actions">
                <a href="view_submissions.php" class="btn btn-primary">📋 Manage Submissions</a>
                <a href="../upload-ps.html" class="btn btn-success">➕ Add New Submission</a>
                <a href="?export=csv" class="btn btn-info">📊 Export Data</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 5 minutes
        setTimeout(() => {
            window.location.reload();
        }, 300000);

        // Add some animation to the charts
        window.addEventListener('load', () => {
            const fills = document.querySelectorAll('.domain-fill');
            fills.forEach(fill => {
                const width = fill.style.width;
                fill.style.width = '0%';
                setTimeout(() => {
                    fill.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>