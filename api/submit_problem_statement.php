<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database_config.php';

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Function to generate unique filename
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Function to validate file upload (optimized)
function validateFile($file, $allowedTypes, $maxSize) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File upload error: ' . $file['error']];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File size exceeds limit'];
    }
    
    // Quick extension check first (faster than mime type check)
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
    
    if (!in_array($extension, $allowedExtensions)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    // Only do mime type check for critical files (optional for performance)
    // Uncomment if you need strict mime type validation
    /*
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    */
    
    return ['valid' => true];
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Validate required fields
    $requiredFields = ['orgName', 'spocName', 'spocContact', 'contactEmail', 'psTitle', 'psDescription'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate logo upload
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception('Organization logo is required');
    }
    
    // Sanitize input data
    $orgName = sanitizeInput($_POST['orgName']);
    $spocName = sanitizeInput($_POST['spocName']);
    $spocContact = sanitizeInput($_POST['spocContact']);
    $contactEmail = filter_var($_POST['contactEmail'], FILTER_VALIDATE_EMAIL);
    $psTitle = sanitizeInput($_POST['psTitle']);
    $psDescription = sanitizeInput($_POST['psDescription']);
    $domain = isset($_POST['domain']) ? sanitizeInput($_POST['domain']) : null;
    $datasetLink = isset($_POST['datasetLink']) ? filter_var($_POST['datasetLink'], FILTER_VALIDATE_URL) : null;
    
    if (!$contactEmail) {
        throw new Exception('Invalid email address');
    }
    
    // Validate and process logo upload
    $logoValidation = validateFile($_FILES['logo'], ALLOWED_LOGO_TYPES, MAX_LOGO_SIZE);
    if (!$logoValidation['valid']) {
        throw new Exception('Logo validation failed: ' . $logoValidation['error']);
    }
    
    $logoOriginalName = $_FILES['logo']['name'];
    $logoFilename = generateUniqueFilename($logoOriginalName);
    $logoPath = LOGO_DIR . $logoFilename;
    
    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
        throw new Exception('Failed to upload logo file');
    }
    
    // Get database connection
    $pdo = getDBConnection();
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert problem statement
    $stmt = $pdo->prepare("
        INSERT INTO problem_statements (
            org_name, spoc_name, spoc_contact, contact_email, ps_title, 
            ps_description, domain, dataset_link, logo_filename, 
            logo_original_name, logo_file_size
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $orgName, $spocName, $spocContact, $contactEmail, $psTitle,
        $psDescription, $domain, $datasetLink, $logoFilename,
        $logoOriginalName, $_FILES['logo']['size']
    ]);
    
    $psId = $pdo->lastInsertId();
    
    // Process supporting documents if any
    $documentsProcessed = 0;
    if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'])) {
        // Handle both single file and multiple files
        $documentFiles = $_FILES['documents'];
        
        // Normalize to array format
        if (!is_array($documentFiles['name'])) {
            // Single file - convert to array format
            $documentFiles = [
                'name' => [$documentFiles['name']],
                'tmp_name' => [$documentFiles['tmp_name']],
                'size' => [$documentFiles['size']],
                'error' => [$documentFiles['error']],
                'type' => [$documentFiles['type']]
            ];
        }
        
        $documentCount = count($documentFiles['name']);
        
        // Prepare statement for document insertion
        $docStmt = $pdo->prepare("
            INSERT INTO supporting_documents (
                ps_id, filename, original_name, file_size, file_type
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        for ($i = 0; $i < $documentCount; $i++) {
            // Skip empty files
            if (empty($documentFiles['name'][$i]) || $documentFiles['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $docFile = [
                'name' => $documentFiles['name'][$i],
                'tmp_name' => $documentFiles['tmp_name'][$i],
                'size' => $documentFiles['size'][$i],
                'error' => $documentFiles['error'][$i],
                'type' => $documentFiles['type'][$i]
            ];
            
            // Validate file
            $docValidation = validateFile($docFile, ALLOWED_DOC_TYPES, MAX_FILE_SIZE);
            if ($docValidation['valid']) {
                $docOriginalName = $docFile['name'];
                $docFilename = generateUniqueFilename($docOriginalName);
                $docPath = DOCS_DIR . $docFilename;
                
                // Move uploaded file
                if (move_uploaded_file($docFile['tmp_name'], $docPath)) {
                    // Determine file type
                    $extension = strtolower(pathinfo($docOriginalName, PATHINFO_EXTENSION));
                    $fileType = $docFile['type'] ?: 'application/' . $extension;
                    
                    // Insert document record
                    $docStmt->execute([
                        $psId, $docFilename, $docOriginalName, $docFile['size'], $fileType
                    ]);
                    
                    $documentsProcessed++;
                }
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Problem statement submitted successfully',
        'submissionId' => $psId,
        'documentsProcessed' => $documentsProcessed
    ]);
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    // Clean up uploaded files on error
    if (isset($logoPath) && file_exists($logoPath)) {
        unlink($logoPath);
    }
    
    // Log error
    error_log("Problem statement submission error: " . $e->getMessage());
    
    // Send error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>