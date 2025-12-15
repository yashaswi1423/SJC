<?php
header('Content-Type: application/json');

// Debug what's being received
$debug_info = [
    'POST_data' => $_POST,
    'FILES_data' => $_FILES,
    'documents_isset' => isset($_FILES['documents']),
    'documents_structure' => isset($_FILES['documents']) ? $_FILES['documents'] : null
];

// Log to file for debugging
file_put_contents('debug_upload.log', date('Y-m-d H:i:s') . " - " . json_encode($debug_info, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

echo json_encode([
    'success' => true,
    'message' => 'Debug info logged',
    'debug' => $debug_info
]);
?>