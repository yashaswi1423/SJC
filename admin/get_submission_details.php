<?php
require_once '../config/database_config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die('Unauthorized');
}

if (!isset($_GET['id'])) {
    die('Invalid request');
}

$psId = (int)$_GET['id'];

try {
    $pdo = getDBConnection();
    
    // Get submission details
    $stmt = $pdo->prepare("SELECT * FROM problem_statements WHERE id = ?");
    $stmt->execute([$psId]);
    $submission = $stmt->fetch();
    
    if (!$submission) {
        die('Submission not found');
    }
    
    // Get supporting documents
    $stmt = $pdo->prepare("SELECT * FROM supporting_documents WHERE ps_id = ?");
    $stmt->execute([$psId]);
    $documents = $stmt->fetchAll();
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

<h2>Problem Statement Details</h2>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <div>
        <h3>Organization Information</h3>
        <p><strong>Organization:</strong> <?= htmlspecialchars($submission['org_name']) ?></p>
        <p><strong>SPOC Name:</strong> <?= htmlspecialchars($submission['spoc_name']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($submission['spoc_contact']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($submission['contact_email']) ?></p>
        <p><strong>Domain:</strong> <?= htmlspecialchars($submission['domain'] ?: 'Not specified') ?></p>
        <?php if ($submission['dataset_link']): ?>
        <p><strong>Dataset Link:</strong> <a href="<?= htmlspecialchars($submission['dataset_link']) ?>" target="_blank">View Dataset</a></p>
        <?php endif; ?>
    </div>
    
    <div>
        <h3>Submission Details</h3>
        <p><strong>Status:</strong> <span class="status <?= $submission['status'] ?>"><?= ucfirst($submission['status']) ?></span></p>
        <p><strong>Submitted:</strong> <?= date('F j, Y \a\t g:i A', strtotime($submission['submission_date'])) ?></p>
        <p><strong>Last Updated:</strong> <?= date('F j, Y \a\t g:i A', strtotime($submission['updated_at'])) ?></p>
        
        <h4>Organization Logo</h4>
        <?php if ($submission['logo_filename']): ?>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center;">
                <?php
                $logoPath = '../uploads/logos/' . $submission['logo_filename'];
                $logoExtension = strtolower(pathinfo($submission['logo_original_name'], PATHINFO_EXTENSION));
                ?>
                
                <?php if (in_array($logoExtension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <img src="<?= $logoPath ?>" alt="Organization Logo" style="max-width: 200px; max-height: 150px;">
                <?php else: ?>
                    <p>📄 <?= htmlspecialchars($submission['logo_original_name']) ?></p>
                    <a href="<?= $logoPath ?>" target="_blank" class="btn btn-info">Download Logo</a>
                <?php endif; ?>
                
                <p><small>File: <?= htmlspecialchars($submission['logo_original_name']) ?> (<?= number_format($submission['logo_file_size'] / 1024, 1) ?> KB)</small></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div style="margin-bottom: 20px;">
    <h3>Problem Statement Title</h3>
    <p style="font-size: 1.1em; font-weight: bold; color: #2b2d73;"><?= htmlspecialchars($submission['ps_title']) ?></p>
</div>

<div style="margin-bottom: 20px;">
    <h3>Problem Statement Description</h3>
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #2b2d73;">
        <?= nl2br(htmlspecialchars($submission['ps_description'])) ?>
    </div>
</div>

<?php if (!empty($documents)): ?>
<div>
    <h3>Supporting Documents (<?= count($documents) ?>)</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
        <?php foreach ($documents as $doc): ?>
        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #f8f9fa;">
            <h4 style="margin: 0 0 10px 0; font-size: 1em;">📎 <?= htmlspecialchars($doc['original_name']) ?></h4>
            <p style="margin: 5px 0; font-size: 0.9em; color: #666;">
                Size: <?= number_format($doc['file_size'] / 1024, 1) ?> KB<br>
                Type: <?= htmlspecialchars($doc['file_type']) ?><br>
                Uploaded: <?= date('M j, Y H:i', strtotime($doc['upload_date'])) ?>
            </p>
            <a href="../uploads/documents/<?= $doc['filename'] ?>" target="_blank" class="btn btn-primary" style="font-size: 0.9em;">Download</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div>
    <h3>Supporting Documents</h3>
    <p style="color: #666; font-style: italic;">No supporting documents uploaded.</p>
</div>
<?php endif; ?>

<style>
.status { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
.status.pending { background: #fff3cd; color: #856404; }
.status.approved { background: #d4edda; color: #155724; }
.status.rejected { background: #f8d7da; color: #721c24; }
.btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; }
.btn-primary { background: #2b2d73; color: white; }
.btn-info { background: #17a2b8; color: white; }
</style>