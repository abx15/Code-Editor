<?php
require_once 'config.php';

setCORSHeaders();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    // Get input data
    $session_id = sanitizeInput($_POST['session'] ?? '');
    $code = $_POST['code'] ?? '';
    $language = sanitizeInput($_POST['language'] ?? 'javascript');

    if (empty($session_id)) {
        throw new Exception('Session ID is required');
    }

    // Get database connection
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Check if session exists
    $stmt = $pdo->prepare("SELECT id FROM code_sessions WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $existingSession = $stmt->fetch();

    if ($existingSession) {
        // Update existing session
        $stmt = $pdo->prepare("
            UPDATE code_sessions 
            SET code = ?, language = ?, last_updated = CURRENT_TIMESTAMP 
            WHERE session_id = ?
        ");
        $stmt->execute([$code, $language, $session_id]);
    } else {
        // Create new session
        $stmt = $pdo->prepare("
            INSERT INTO code_sessions (session_id, code, language) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$session_id, $code, $language]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Code saved successfully',
        'session_id' => $session_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    logError("Save Code Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save code: ' . $e->getMessage()
    ]);
}
?>