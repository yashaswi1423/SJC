<?php
require_once 'config/database_config.php';

// Set content type to HTML
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p style="color: red;">Invalid submission ID.</p>';
    exit;
}

$submissionId = (int)$_GET['id'];

try {
    $pdo = getDBConnection();
    
    // Get submission details
    $stmt = $pdo->prepare("SELECT * FROM problem_statements WHERE id = ?");
    $stmt->execute([$submissionId]);
    $submission = $stmt->fetch();
    
    if (!$submission) {
        echo '<p style="color: red;">Submission not found.</p>';
        exit;
    }
    
    // Get supporting documents
    $stmt = $pdo->prepare("SELECT * FROM supporting_documents WHERE ps_id = ? ORDER BY upload_date");
    $stmt->execute([$submissionId]);
    $documents = $stmt->fetchAll();
    
    // Helper function to escape HTML
    function escapeHtml($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    // Helper function to format file size
    function formatFileSize($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    // Check if logo is an image
    $logoPath = 'uploads/logos/' . $submission['logo_filename'];
    $logoExtension = strtolower(pathinfo($submission['logo_original_name'], PATHINFO_EXTENSION));
    $isImage = in_array($logoExtension, ['jpg', 'jpeg', 'png', 'gif']);
    
    ?>
    
    <style>
        .detail-section { margin: 1.5rem 0; padding: 1rem; background: #f8f9fa; border-radius: 8px; }
        .detail-section h4 { color: #2b2d73; margin-bottom: 1rem; font-size: 1.2rem; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
        .detail-item { margin: 0.5rem 0; }
        .detail-label { font-weight: 600; color: #555; }
        .detail-value { margin-top: 0.25rem; }
        .problem-description { background: white; padding: 1rem; border-radius: 6px; border-left: 4px solid #2b2d73; line-height: 1.6; }
        .file-list { display: grid; gap: 1rem; margin-top: 1rem; }
        .file-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border-radius: 8px; border: 1px solid #dee2e6; }
        .file-icon { font-size: 2rem; }
        .file-info { flex: 1; }
        .file-name { font-weight: 600; margin-bottom: 0.25rem; }
        .file-size { color: #666; font-size: 0.9rem; }
        .download-btn { background: #007bff; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        .download-btn:hover { background: #0056b3; color: white; }
        .status-badge { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .logo-preview { max-width: 200px; max-height: 150px; border-radius: 8px; border: 1px solid #dee2e6; }
        @media (max-width: 768px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
    
    <div class="detail-section">
        <h4>🏢 Organization Information</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Organization Name:</div>
                <div class="detail-value"><strong><?= escapeHtml($submission['org_name']) ?></strong></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Submission Status:</div>
                <div class="detail-value">
                    <span class="status-badge status-<?= $submission['status'] ?>">
                        <?= ucfirst($submission['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="detail-section">
        <h4>👤 Contact Information</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">SPOC Name:</div>
                <div class="detail-value"><?= escapeHtml($submission['spoc_name']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Contact Number:</div>
                <div class="detail-value">📞 <?= escapeHtml($submission['spoc_contact']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email Address:</div>
                <div class="detail-value">✉️ <?= escapeHtml($submission['contact_email']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Domain/Category:</div>
                <div class="detail-value"><?= escapeHtml($submission['domain'] ?: 'Not specified') ?></div>
            </div>
        </div>
        
        <?php if ($submission['dataset_link']): ?>
        <div class="detail-item">
            <div class="detail-label">Dataset/Reference Link:</div>
            <div class="detail-value">
                <a href="<?= escapeHtml($submission['dataset_link']) ?>" target="_blank" style="color: #007bff;">
                    <?= escapeHtml($submission['dataset_link']) ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="detail-section">
        <h4>📝 Problem Statement</h4>
        <div class="detail-item">
            <div class="detail-label">Title:</div>
            <div class="detail-value"><strong><?= escapeHtml($submission['ps_title']) ?></strong></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Description:</div>
            <div class="problem-description">
                <?= nl2br(escapeHtml($submission['ps_description'])) ?>
            </div>
        </div>
    </div>
    
    <div class="detail-section">
        <h4>🏛️ Organization Logo</h4>
        <div class="file-item">
            <div class="file-icon">
                <?php if ($isImage && file_exists($logoPath)): ?>
                    <img src="<?= $logoPath ?>" alt="Organization Logo" class="logo-preview">
                <?php else: ?>
                    📄
                <?php endif; ?>
            </div>
            <div class="file-info">
                <div class="file-name"><?= escapeHtml($submission['logo_original_name']) ?></div>
                <div class="file-size"><?= formatFileSize($submission['logo_file_size']) ?></div>
                <div style="font-size: 0.8em; color: #666; margin-top: 0.25rem;">
                    Uploaded: <?= date('M j, Y g:i A', strtotime($submission['submission_date'])) ?>
                </div>
            </div>
            <a href="<?= $logoPath ?>" target="_blank" class="download-btn">📥 Download</a>
        </div>
    </div>
    
    <?php if (!empty($documents)): ?>
    <div class="detail-section">
        <h4>📎 Supporting Documents (<?= count($documents) ?>)</h4>
        <div class="file-list">
            <?php foreach ($documents as $doc): ?>
            <div class="file-item">
                <div class="file-icon">📄</div>
                <div class="file-info">
                    <div class="file-name"><?= escapeHtml($doc['original_name']) ?></div>
                    <div class="file-size"><?= formatFileSize($doc['file_size']) ?></div>
                    <div style="font-size: 0.8em; color: #666; margin-top: 0.25rem;">
                        Uploaded: <?= date('M j, Y g:i A', strtotime($doc['upload_date'])) ?>
                    </div>
                </div>
                <a href="uploads/documents/<?= $doc['filename'] ?>" target="_blank" class="download-btn">📥 Download</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="detail-section">
        <h4>📎 Supporting Documents</h4>
        <p style="color: #666; font-style: italic;">No supporting documents were uploaded with this submission.</p>
    </div>
    <?php endif; ?>
    
    <div class="detail-section">
        <h4>📅 Submission Timeline</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Submitted On:</div>
                <div class="detail-value"><?= date('F j, Y', strtotime($submission['submission_date'])) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Submitted At:</div>
                <div class="detail-value"><?= date('g:i A', strtotime($submission['submission_date'])) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Last Updated:</div>
                <div class="detail-value"><?= date('M j, Y g:i A', strtotime($submission['updated_at'])) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Submission ID:</div>
                <div class="detail-value"><strong>#<?= $submission['id'] ?></strong></div>
            </div>
        </div>
    </div>
    
    <?php
    
} catch (Exception $e) {
    echo '<div style="color: red; padding: 1rem; background: #f8d7da; border-radius: 4px;">';
    echo '<strong>Error:</strong> ' . escapeHtml($e->getMessage());
    echo '</div>';
}
?>