<?php
require_once 'config/database_config.php';

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
            <title>XAMPP Admin Login - Smart Horizon Hackathon</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #2b2d73, #c12d6b); margin: 0; padding: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .login-container { background: white; padding: 3rem; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); max-width: 400px; width: 100%; }
                .login-header { text-align: center; margin-bottom: 2rem; }
                .login-header h1 { color: #2b2d73; margin: 0; font-size: 1.8rem; }
                .login-header p { color: #666; margin: 0.5rem 0 0 0; }
                .form-group { margin: 1.5rem 0; }
                label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; }
                input[type="password"] { width: 100%; padding: 1rem; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease; }
                input[type="password"]:focus { outline: none; border-color: #2b2d73; box-shadow: 0 0 0 3px rgba(43, 45, 115, 0.1); }
                .login-btn { width: 100%; background: #2b2d73; color: white; padding: 1rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.3s ease; }
                .login-btn:hover { background: #1f225e; }
                .login-hint { text-align: center; margin-top: 1rem; color: #666; font-size: 0.9rem; }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-header">
                    <h1>🔐 XAMPP Admin Panel</h1>
                    <p>Smart Horizon Hackathon 2026</p>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="admin_password" placeholder="Enter admin password" required>
                    </div>
                    <button type="submit" class="login-btn">🚀 Access Admin Panel</button>
                </form>
                <div class="login-hint">
                    <strong>Default Password:</strong> hackathon2026
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

try {
    $pdo = getDBConnection();
    
    // Handle status updates
    if (isset($_POST['update_status'])) {
        $psId = (int)$_POST['ps_id'];
        $newStatus = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE problem_statements SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$newStatus, $psId]);
        
        echo "<script>alert('Status updated successfully!'); window.location.reload();</script>";
    }
    
    // Get all submissions with document count
    $stmt = $pdo->query("
        SELECT ps.*, COUNT(sd.id) as document_count 
        FROM problem_statements ps 
        LEFT JOIN supporting_documents sd ON ps.id = sd.ps_id 
        GROUP BY ps.id 
        ORDER BY ps.submission_date DESC
    ");
    $submissions = $stmt->fetchAll();
    
    // Get statistics
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM problem_statements
    ")->fetch();
    
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XAMPP Admin Dashboard - Smart Horizon Hackathon</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; color: #333; }
        
        .header { background: linear-gradient(135deg, #2b2d73, #c12d6b); color: white; padding: 2rem 0; }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .header p { opacity: 0.9; }
        
        .nav-bar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 1rem 0; }
        .nav-content { max-width: 1200px; margin: 0 auto; padding: 0 2rem; display: flex; justify-content: space-between; align-items: center; }
        .nav-links { display: flex; gap: 2rem; }
        .nav-link { text-decoration: none; color: #2b2d73; font-weight: 600; padding: 0.5rem 1rem; border-radius: 6px; transition: background 0.3s ease; }
        .nav-link:hover { background: #f8f9fa; }
        .logout-btn { background: #dc3545; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem; }
        .stat-label { color: #666; font-weight: 600; }
        .stat-total { color: #2b2d73; }
        .stat-pending { color: #ffc107; }
        .stat-approved { color: #28a745; }
        .stat-rejected { color: #dc3545; }
        
        .submissions-section { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .section-header { background: #2b2d73; color: white; padding: 1.5rem; }
        .section-title { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .section-subtitle { opacity: 0.9; }
        
        .submissions-table { width: 100%; border-collapse: collapse; }
        .submissions-table th { background: #f8f9fa; padding: 1rem; text-align: left; font-weight: 600; border-bottom: 2px solid #dee2e6; }
        .submissions-table td { padding: 1rem; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        .submissions-table tr:hover { background: #f8f9fa; }
        
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .action-btn { padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin: 0.2rem; transition: all 0.3s ease; }
        .btn-view { background: #007bff; color: white; }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .action-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        
        .contact-info { font-size: 0.9rem; }
        .contact-info div { margin: 0.2rem 0; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 2% auto; padding: 2rem; border-radius: 12px; max-width: 800px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        
        .detail-section { margin: 1.5rem 0; }
        .detail-section h4 { color: #2b2d73; margin-bottom: 0.5rem; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        
        @media (max-width: 768px) {
            .nav-content { flex-direction: column; gap: 1rem; }
            .nav-links { flex-wrap: wrap; }
            .detail-grid { grid-template-columns: 1fr; }
            .submissions-table { font-size: 0.9rem; }
            .submissions-table th, .submissions-table td { padding: 0.5rem; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>🏛️ XAMPP Admin Dashboard</h1>
            <p>Smart Horizon 48-Hour International Hackathon 2026 - Problem Statement Management</p>
        </div>
    </header>
    
    <nav class="nav-bar">
        <div class="nav-content">
            <div class="nav-links">
                <a href="index.html" class="nav-link">🏠 Home</a>
                <a href="upload-ps-php.html" class="nav-link">📝 Submit (PHP)</a>
                <a href="admin_panel.html" class="nav-link">⚡ Node.js Admin</a>
                <a href="system_status.html" class="nav-link">📊 System Status</a>
            </div>
            <form method="POST" style="display: inline;">
                <button type="submit" name="logout" class="logout-btn">🚪 Logout</button>
            </form>
        </div>
    </nav>
    
    <?php if (isset($_POST['logout'])) { session_destroy(); header('Location: ' . $_SERVER['PHP_SELF']); exit; } ?>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number stat-total"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Submissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-pending"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-approved"><?= $stats['approved'] ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-rejected"><?= $stats['rejected'] ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
        
        <div class="submissions-section">
            <div class="section-header">
                <div class="section-title">📋 Problem Statement Submissions</div>
                <div class="section-subtitle">Manage and review all submitted problem statements</div>
            </div>
            
            <?php if (empty($submissions)): ?>
                <div style="padding: 3rem; text-align: center; color: #666;">
                    <h3>📭 No Submissions Yet</h3>
                    <p>No problem statements have been submitted yet.</p>
                    <a href="upload-ps-php.html" style="color: #2b2d73; text-decoration: none; font-weight: 600;">Submit the first one →</a>
                </div>
            <?php else: ?>
                <table class="submissions-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Organization</th>
                            <th>Problem Statement</th>
                            <th>Contact Info</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><strong>#<?= $submission['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($submission['org_name']) ?></strong>
                                    <br><small>📄 <?= $submission['document_count'] ?> docs</small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(substr($submission['ps_title'], 0, 50)) ?><?= strlen($submission['ps_title']) > 50 ? '...' : '' ?></strong>
                                    <br><small><?= htmlspecialchars($submission['domain'] ?: 'No domain') ?></small>
                                </td>
                                <td class="contact-info">
                                    <div><strong><?= htmlspecialchars($submission['spoc_name']) ?></strong></div>
                                    <div>📞 <?= htmlspecialchars($submission['spoc_contact']) ?></div>
                                    <div>✉️ <?= htmlspecialchars($submission['contact_email']) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $submission['status'] ?>">
                                        <?= ucfirst($submission['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('M j, Y', strtotime($submission['submission_date'])) ?>
                                    <br><small><?= date('g:i A', strtotime($submission['submission_date'])) ?></small>
                                </td>
                                <td>
                                    <button class="action-btn btn-view" onclick="viewSubmission(<?= $submission['id'] ?>)">👁️ View</button>
                                    <?php if ($submission['status'] !== 'approved'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="ps_id" value="<?= $submission['id'] ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" name="update_status" class="action-btn btn-approve" onclick="return confirm('Approve this submission?')">✅ Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($submission['status'] !== 'rejected'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="ps_id" value="<?= $submission['id'] ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" name="update_status" class="action-btn btn-reject" onclick="return confirm('Reject this submission?')">❌ Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal for viewing submission details -->
    <div id="submissionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Submission Details</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalContent">
                Loading...
            </div>
        </div>
    </div>
    
    <script>
        function viewSubmission(id) {
            document.getElementById('submissionModal').style.display = 'block';
            document.getElementById('modalContent').innerHTML = 'Loading...';
            
            fetch(`get_submission_details.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('modalContent').innerHTML = '<p style="color: red;">Error loading submission details.</p>';
                });
        }
        
        function closeModal() {
            document.getElementById('submissionModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('submissionModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>