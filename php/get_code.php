<?php
require_once 'config.php';

setCORSHeaders();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Only GET method allowed');
    }

    // Get session ID from query parameter
    $session_id = sanitizeInput($_GET['session'] ?? '');

    if (empty($session_id)) {
        throw new Exception('Session ID is required');
    }

    // Get database connection
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Fetch code from database
    $stmt = $pdo->prepare("
        SELECT code, language, last_updated 
        FROM code_sessions 
        WHERE session_id = ? 
        ORDER BY last_updated DESC 
        LIMIT 1
    ");
    $stmt->execute([$session_id]);
    $result = $stmt->fetch();

    if ($result) {
        echo json_encode([
            'success' => true,
            'code' => $result['code'],
            'language' => $result['language'],
            'last_updated' => $result['last_updated'],
            'session_id' => $session_id
        ]);
    } else {
        // Return default code for new sessions
        echo json_encode([
            'success' => true,
            'code' => "// Welcome to Live Code Editor with AI Help!\n// Start coding here...\n\nconsole.log('Hello World!');",
            'language' => 'javascript',
            'last_updated' => date('Y-m-d H:i:s'),
            'session_id' => $session_id
        ]);
    }

} catch (Exception $e) {
    logError("Get Code Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve code: ' . $e->getMessage(),
        'code' => "// Error loading code\nconsole.log('Failed to load saved code');",
        'language' => 'javascript'
    ]);
}
?>