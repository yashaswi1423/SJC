<?php
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
            <title>Database Browser - Smart Horizon Hackathon</title>
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
                <h2>Database Browser Login</h2>
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
    
    // Get table information
    $tables = [];
    if (DB_TYPE === 'mysql') {
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
    } else {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        while ($row = $stmt->fetch()) {
            $tables[] = $row['name'];
        }
    }
    
    // Handle table selection
    $selectedTable = $_GET['table'] ?? '';
    $tableData = [];
    $tableColumns = [];
    $tableInfo = [];
    
    if ($selectedTable && in_array($selectedTable, $tables)) {
        // Get table structure
        if (DB_TYPE === 'mysql') {
            $stmt = $pdo->query("DESCRIBE `$selectedTable`");
            $tableColumns = $stmt->fetchAll();
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$selectedTable`");
            $tableInfo['row_count'] = $stmt->fetch()['count'];
        } else {
            $stmt = $pdo->query("PRAGMA table_info(`$selectedTable`)");
            $tableColumns = $stmt->fetchAll();
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$selectedTable`");
            $tableInfo['row_count'] = $stmt->fetch()['count'];
        }
        
        // Get table data with pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $stmt = $pdo->query("SELECT * FROM `$selectedTable` LIMIT $limit OFFSET $offset");
        $tableData = $stmt->fetchAll();
        
        $tableInfo['total_pages'] = ceil($tableInfo['row_count'] / $limit);
        $tableInfo['current_page'] = $page;
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Browser - Smart Horizon Hackathon</title>
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
        
        .sidebar { width: 250px; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); float: left; margin-right: 20px; }
        .sidebar h3 { color: #2b2d73; margin-bottom: 15px; }
        .table-list { list-style: none; }
        .table-list li { margin-bottom: 8px; }
        .table-list a { color: #666; text-decoration: none; padding: 8px 12px; display: block; border-radius: 5px; transition: all 0.3s; }
        .table-list a:hover, .table-list a.active { background: #2b2d73; color: white; }
        
        .main-content { margin-left: 270px; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); min-height: 600px; }
        
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #eee; }
        .table-title { color: #2b2d73; font-size: 1.5em; }
        .table-stats { color: #666; font-size: 0.9em; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .data-table th { background: #f8f9fa; color: #2b2d73; font-weight: bold; position: sticky; top: 0; }
        .data-table tr:hover { background: #f8f9fa; }
        .data-table td { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        .column-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .column-info h4 { color: #2b2d73; margin-bottom: 10px; }
        .column-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .column-item { background: white; padding: 10px; border-radius: 5px; border-left: 4px solid #2b2d73; }
        .column-name { font-weight: bold; color: #2b2d73; }
        .column-type { color: #666; font-size: 0.9em; }
        
        .pagination { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; text-decoration: none; color: #2b2d73; }
        .pagination a:hover { background: #2b2d73; color: white; }
        .pagination .current { background: #2b2d73; color: white; border-color: #2b2d73; }
        
        .no-data { text-align: center; color: #666; font-style: italic; padding: 40px; }
        
        .search-box { margin-bottom: 20px; }
        .search-box input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
        
        .export-btn { background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .export-btn:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗄️ Database Browser</h1>
        <div class="header-nav">
            <a href="view_submissions.php">📋 View Submissions</a>
            <a href="database_dashboard.php">📊 Dashboard</a>
            <a href="database_browser.php">🗄️ Browser</a>
            <a href="../index.html">🏠 Home</a>
        </div>
        <a href="?logout=1" class="logout">Logout</a>
        <?php if (isset($_GET['logout'])) { session_destroy(); header('Location: database_browser.php'); exit; } ?>
    </div>

    <div class="container">
        <div class="sidebar">
            <h3>📋 Tables</h3>
            <ul class="table-list">
                <?php foreach ($tables as $table): ?>
                <li>
                    <a href="?table=<?= urlencode($table) ?>" 
                       class="<?= $selectedTable === $table ? 'active' : '' ?>">
                        <?= htmlspecialchars($table) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <?php if (empty($tables)): ?>
            <p class="no-data">No tables found</p>
            <?php endif; ?>
        </div>

        <div class="main-content">
            <?php if ($selectedTable): ?>
                <div class="table-header">
                    <div>
                        <h2 class="table-title"><?= htmlspecialchars($selectedTable) ?></h2>
                        <div class="table-stats">
                            <?= number_format($tableInfo['row_count']) ?> rows total
                            <?php if ($tableInfo['total_pages'] > 1): ?>
                            | Page <?= $tableInfo['current_page'] ?> of <?= $tableInfo['total_pages'] ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <a href="?table=<?= urlencode($selectedTable) ?>&export=csv" class="export-btn">📊 Export CSV</a>
                    </div>
                </div>

                <!-- Column Information -->
                <div class="column-info">
                    <h4>📋 Table Structure</h4>
                    <div class="column-grid">
                        <?php foreach ($tableColumns as $column): ?>
                        <div class="column-item">
                            <div class="column-name">
                                <?= htmlspecialchars(DB_TYPE === 'mysql' ? $column['Field'] : $column['name']) ?>
                            </div>
                            <div class="column-type">
                                <?= htmlspecialchars(DB_TYPE === 'mysql' ? $column['Type'] : $column['type']) ?>
                                <?php if (DB_TYPE === 'mysql' && $column['Key'] === 'PRI'): ?>
                                    <span style="color: #c12d6b; font-weight: bold;">🔑 PRIMARY</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Data Table -->
                <?php if (!empty($tableData)): ?>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php foreach (array_keys($tableData[0]) as $column): ?>
                                <th><?= htmlspecialchars($column) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableData as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                <td title="<?= htmlspecialchars($value) ?>">
                                    <?php if (is_null($value)): ?>
                                        <em style="color: #999;">NULL</em>
                                    <?php elseif (strlen($value) > 50): ?>
                                        <?= htmlspecialchars(substr($value, 0, 50)) ?>...
                                    <?php else: ?>
                                        <?= htmlspecialchars($value) ?>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($tableInfo['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php if ($tableInfo['current_page'] > 1): ?>
                    <a href="?table=<?= urlencode($selectedTable) ?>&page=<?= $tableInfo['current_page'] - 1 ?>">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $tableInfo['current_page'] - 2); $i <= min($tableInfo['total_pages'], $tableInfo['current_page'] + 2); $i++): ?>
                        <?php if ($i === $tableInfo['current_page']): ?>
                        <span class="current"><?= $i ?></span>
                        <?php else: ?>
                        <a href="?table=<?= urlencode($selectedTable) ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($tableInfo['current_page'] < $tableInfo['total_pages']): ?>
                    <a href="?table=<?= urlencode($selectedTable) ?>&page=<?= $tableInfo['current_page'] + 1 ?>">Next →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="no-data">
                    <h3>No data found in this table</h3>
                    <p>The table exists but contains no records.</p>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-data">
                    <h3>🗄️ Database Browser</h3>
                    <p>Select a table from the sidebar to view its contents and structure.</p>
                    <br>
                    <p><strong>Available Tables:</strong></p>
                    <ul style="text-align: left; display: inline-block;">
                        <?php foreach ($tables as $table): ?>
                        <li><a href="?table=<?= urlencode($table) ?>" style="color: #2b2d73;"><?= htmlspecialchars($table) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add search functionality
        function addSearchBox() {
            const mainContent = document.querySelector('.main-content');
            const tableHeader = document.querySelector('.table-header');
            
            if (tableHeader) {
                const searchBox = document.createElement('div');
                searchBox.className = 'search-box';
                searchBox.innerHTML = '<input type="text" placeholder="Search in table data..." id="tableSearch">';
                
                tableHeader.parentNode.insertBefore(searchBox, tableHeader.nextSibling);
                
                document.getElementById('tableSearch').addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('.data-table tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        }
        
        // Initialize search box if we have a table selected
        if (document.querySelector('.data-table')) {
            addSearchBox();
        }
    </script>
</body>
</html>

<?php
// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $selectedTable) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $selectedTable . '_export.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    if (!empty($tableData)) {
        fputcsv($output, array_keys($tableData[0]));
        
        // Add data
        foreach ($tableData as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}
?>