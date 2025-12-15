<?php
require_once '../config/database_config.php';

// Simple authentication (you should implement proper authentication)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    // Simple login form
    if (isset($_POST['admin_password']) && $_POST['admin_password'] === 'hackathon2026') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login - Smart Horizon Hackathon</title>
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
                <h2>Admin Login</h2>
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
    
    // Handle status updates
    if (isset($_POST['update_status'])) {
        $psId = (int)$_POST['ps_id'];
        $newStatus = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE problem_statements SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $psId]);
        
        echo "<script>alert('Status updated successfully'); window.location.reload();</script>";
    }
    
    // Get all submissions with document counts
    $stmt = $pdo->prepare("
        SELECT ps.*, 
               COUNT(sd.id) as document_count
        FROM problem_statements ps
        LEFT JOIN supporting_documents sd ON ps.id = sd.ps_id
        GROUP BY ps.id
        ORDER BY ps.submission_date DESC
    ");
    $stmt->execute();
    $submissions = $stmt->fetchAll();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Problem Statement Submissions - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #2b2d73; color: white; padding: 20px; margin: -20px -20px 20px -20px; }
        .header h1 { margin: 0; }
        .logout { float: right; background: #c12d6b; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #2b2d73; }
        .submissions-table { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2b2d73; color: white; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.approved { background: #d4edda; color: #155724; }
        .status.rejected { background: #f8d7da; color: #721c24; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; }
        .btn-primary { background: #2b2d73; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 80%; max-width: 800px; border-radius: 10px; max-height: 80vh; overflow-y: auto; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Smart Horizon Hackathon - Admin Panel</h1>
        <a href="?logout=1" class="logout">Logout</a>
        <?php if (isset($_GET['logout'])) { session_destroy(); header('Location: view_submissions.php'); exit; } ?>
    </div>

    <?php
    // Calculate statistics
    $totalSubmissions = count($submissions);
    $pendingCount = count(array_filter($submissions, fn($s) => $s['status'] === 'pending'));
    $approvedCount = count(array_filter($submissions, fn($s) => $s['status'] === 'approved'));
    $rejectedCount = count(array_filter($submissions, fn($s) => $s['status'] === 'rejected'));
    ?>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?= $totalSubmissions ?></div>
            <div>Total Submissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $pendingCount ?></div>
            <div>Pending Review</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $approvedCount ?></div>
            <div>Approved</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $rejectedCount ?></div>
            <div>Rejected</div>
        </div>
    </div>

    <div class="submissions-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Organization</th>
                    <th>SPOC Name</th>
                    <th>Email</th>
                    <th>Problem Title</th>
                    <th>Domain</th>
                    <th>Documents</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                <tr>
                    <td><?= $submission['id'] ?></td>
                    <td><?= htmlspecialchars($submission['org_name']) ?></td>
                    <td><?= htmlspecialchars($submission['spoc_name']) ?></td>
                    <td><?= htmlspecialchars($submission['contact_email']) ?></td>
                    <td><?= htmlspecialchars(substr($submission['ps_title'], 0, 50)) ?>...</td>
                    <td><?= htmlspecialchars($submission['domain'] ?: 'N/A') ?></td>
                    <td><?= $submission['document_count'] ?> files</td>
                    <td>
                        <span class="status <?= $submission['status'] ?>">
                            <?= ucfirst($submission['status']) ?>
                        </span>
                    </td>
                    <td><?= date('M j, Y H:i', strtotime($submission['submission_date'])) ?></td>
                    <td>
                        <button class="btn btn-info" onclick="viewDetails(<?= $submission['id'] ?>)">View</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="ps_id" value="<?= $submission['id'] ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?= $submission['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $submission['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= $submission['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for viewing details -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent">Loading...</div>
        </div>
    </div>

    <script>
        function viewDetails(psId) {
            document.getElementById('detailsModal').style.display = 'block';
            fetch('get_submission_details.php?id=' + psId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = data;
                });
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('detailsModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>