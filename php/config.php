<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');  // Change this
define('DB_PASS', 'your_password');  // Change this
define('DB_NAME', 'live_code_editor');

// OpenAI API Configuration
define('OPENAI_API_KEY', 'your_openai_api_key_here'); // Get from https://platform.openai.com/api-keys

// Create database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// CORS headers for AJAX requests
function setCORSHeaders() {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Generate unique session ID
function generateSessionId() {
    return 'session_' . uniqid() . '_' . time();
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Log errors
function logError($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, 3, "errors.log");
}
?>