<?php
require_once 'config.php';

setCORSHeaders();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    // Get input data
    $code = $_POST['code'] ?? '';
    $language = sanitizeInput($_POST['language'] ?? 'javascript');
    $type = sanitizeInput($_POST['type'] ?? 'suggestion');
    $session_id = sanitizeInput($_POST['session'] ?? '');

    if (empty($code)) {
        throw new Exception('Code is required for AI analysis');
    }

    // Check if OpenAI API key is configured
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY) || OPENAI_API_KEY === 'your_openai_api_key_here') {
        // Use offline AI suggestions when API key is not available
        $offlineSuggestion = getOfflineAISuggestion($code, $language, $type);
        echo json_encode([
            'success' => true,
            'suggestion' => $offlineSuggestion,
            'type' => $type,
            'mode' => 'offline',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Prepare prompt based on request type
    $prompts = [
        'suggestion' => "Analyze this {$language} code and provide helpful suggestions for improvement, optimization, or best practices:\n\n{$code}",
        'fix' => "Find and fix any errors or issues in this {$language} code. Explain what was wrong and how to fix it:\n\n{$code}",
        'explain' => "Explain what this {$language} code does in simple terms. Break down the logic and functionality:\n\n{$code}"
    ];

    $prompt = $prompts[$type] ?? $prompts['suggestion'];

    // Make API call to OpenAI
    $response = callOpenAI($prompt);

    if (!$response) {
        throw new Exception('Failed to get AI response');
    }

    // Save AI suggestion to database
    saveAISuggestion($session_id, $code, $response, $type);

    echo json_encode([
        'success' => true,
        'suggestion' => $response,
        'type' => $type,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    logError("AI Helper Error: " . $e->getMessage());
    
    // Provide fallback suggestions when AI is not available
    $fallbackSuggestions = [
        'suggestion' => getFallbackSuggestion($language),
        'fix' => "AI service is currently unavailable. Please check your code manually for syntax errors, missing semicolons, undefined variables, or incorrect function calls.",
        'explain' => "AI service is currently unavailable. Try breaking down your code step by step: identify variables, functions, loops, and conditions to understand the flow."
    ];

    echo json_encode([
        'success' => false,
        'suggestion' => $fallbackSuggestions[$type] ?? 'AI service temporarily unavailable.',
        'type' => $type,
        'message' => $e->getMessage()
    ]);
}

function callOpenAI($prompt) {
    $apiKey = OPENAI_API_KEY;
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful coding assistant. Provide clear, concise, and practical advice for code improvement, debugging, or explanation. Keep responses under 200 words.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 300,
        'temperature' => 0.7
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        logError("OpenAI API Error: HTTP $httpCode - $response");
        return false;
    }

    $responseData = json_decode($response, true);
    
    if (isset($responseData['choices'][0]['message']['content'])) {
        return trim($responseData['choices'][0]['message']['content']);
    }

    return false;
}

function saveAISuggestion($session_id, $user_code, $ai_suggestion, $type) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) return false;

        $stmt = $pdo->prepare("
            INSERT INTO ai_suggestions (session_id, user_code, ai_suggestion, suggestion_type) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$session_id, $user_code, $ai_suggestion, $type]);
        return true;
    } catch (Exception $e) {
        logError("Save AI Suggestion Error: " . $e->getMessage());
        return false;
    }
}

function getOfflineAISuggestion($code, $language, $type) {
    // Advanced offline AI suggestions based on code analysis
    switch ($type) {
        case 'suggestion':
            return analyzeCodeForSuggestions($code, $language);
        case 'fix':
            return analyzeCodeForErrors($code, $language);
        case 'explain':
            return explainCodeOffline($code, $language);
        default:
            return getFallbackSuggestion($language);
    }
}

function analyzeCodeForSuggestions($code, $language) {
    $suggestions = [];
    
    if ($language === 'javascript') {
        // Check for var usage
        if (strpos($code, 'var ') !== false) {
            $suggestions[] = "Consider using 'let' or 'const' instead of 'var' for better scoping.";
        }
        
        // Check for console.log
        if (strpos($code, 'console.log') !== false) {
            $suggestions[] = "Remember to remove console.log statements before production.";
        }
        
        // Check for == vs ===
        if (strpos($code, ' == ') !== false) {
            $suggestions[] = "Use '===' for strict equality comparison instead of '=='.";
        }
        
        // Check for function declarations
        if (preg_match('/function\s+\w+\s*\(/', $code)) {
            $suggestions[] = "Consider using arrow functions (=>) for shorter syntax where appropriate.";
        }
        
        // Check for missing semicolons
        $lines = explode("\n", $code);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed) && !preg_match('/[{};]$/', $trimmed) && !preg_match('/^(if|for|while|function|class|\/\/)/', $trimmed)) {
                $suggestions[] = "Consider adding semicolons at the end of statements.";
                break;
            }
        }
    }
    
    if ($language === 'php') {
        // Check for PHP opening tag
        if (strpos($code, '<?php') === false && strpos($code, '<?') === false) {
            $suggestions[] = "Add PHP opening tag '<?php' at the beginning.";
        }
        
        // Check for SQL injection vulnerabilities
        if (strpos($code, '$_GET') !== false || strpos($code, '$_POST') !== false) {
            $suggestions[] = "Always sanitize user input to prevent security vulnerabilities.";
        }
        
        // Check for error handling
        if (strpos($code, 'try') === false && (strpos($code, 'mysql') !== false || strpos($code, 'PDO') !== false)) {
            $suggestions[] = "Add try-catch blocks for database operations.";
        }
    }
    
    if ($language === 'python') {
        // Check for indentation
        if (strpos($code, '\t') !== false) {
            $suggestions[] = "Use 4 spaces instead of tabs for indentation (PEP 8).";
        }
        
        // Check for print statements
        if (strpos($code, 'print ') !== false) {
            $suggestions[] = "Use print() function syntax instead of print statement.";
        }
    }
    
    if (empty($suggestions)) {
        $suggestions[] = "Your code looks good! Consider adding comments for better readability.";
        $suggestions[] = "Make sure to test your code with different inputs.";
    }
    
    return implode(" ", array_slice($suggestions, 0, 3)); // Limit to 3 suggestions
}

function analyzeCodeForErrors($code, $language) {
    $errors = [];
    
    if ($language === 'javascript') {
        // Check for common syntax errors
        $openBraces = substr_count($code, '{');
        $closeBraces = substr_count($code, '}');
        if ($openBraces !== $closeBraces) {
            $errors[] = "Mismatched curly braces detected. Check your opening and closing braces.";
        }
        
        $openParens = substr_count($code, '(');
        $closeParens = substr_count($code, ')');
        if ($openParens !== $closeParens) {
            $errors[] = "Mismatched parentheses detected.";
        }
        
        // Check for undefined variables (basic check)
        if (preg_match('/console\.log\(\s*(\w+)\s*\)/', $code, $matches)) {
            $varName = $matches[1];
            if (!preg_match('/(?:var|let|const)\s+' . $varName . '\s*=/', $code)) {
                $errors[] = "Variable '$varName' may be undefined. Make sure to declare it first.";
            }
        }
    }
    
    if ($language === 'php') {
        // Check for PHP syntax
        if (strpos($code, '
    $suggestions = [
        'javascript' => "Consider using 'const' or 'let' instead of 'var', add semicolons after statements, use arrow functions for cleaner code, and handle errors with try-catch blocks.",
        'python' => "Follow PEP 8 style guidelines, use meaningful variable names, add docstrings to functions, and consider using list comprehensions for better readability.",
        'php' => "Use prepared statements for database queries, validate user input, follow PSR coding standards, and use proper error handling with try-catch blocks.",
        'html' => "Use semantic HTML5 elements, add alt attributes to images, ensure proper document structure, and validate your HTML markup.",
        'css' => "Use CSS Grid or Flexbox for layouts, organize your styles logically, use CSS variables for consistency, and consider mobile-first responsive design."
    ];

    return $suggestions[$language] ?? "Write clean, readable code with proper indentation, meaningful variable names, and appropriate comments.";
}
?>) === false && strpos($code, 'echo') !== false) {
            $errors[] = "PHP variables should start with '
    $suggestions = [
        'javascript' => "Consider using 'const' or 'let' instead of 'var', add semicolons after statements, use arrow functions for cleaner code, and handle errors with try-catch blocks.",
        'python' => "Follow PEP 8 style guidelines, use meaningful variable names, add docstrings to functions, and consider using list comprehensions for better readability.",
        'php' => "Use prepared statements for database queries, validate user input, follow PSR coding standards, and use proper error handling with try-catch blocks.",
        'html' => "Use semantic HTML5 elements, add alt attributes to images, ensure proper document structure, and validate your HTML markup.",
        'css' => "Use CSS Grid or Flexbox for layouts, organize your styles logically, use CSS variables for consistency, and consider mobile-first responsive design."
    ];

    return $suggestions[$language] ?? "Write clean, readable code with proper indentation, meaningful variable names, and appropriate comments.";
}
?> symbol.";
        }
        
        // Check for missing semicolons
        $lines = explode("\n", $code);
        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);
            if (!empty($trimmed) && !preg_match('/[{};]$/', $trimmed) && !preg_match('/^(if|for|while|function|class|\/\/)/', $trimmed) && strpos($trimmed, '<?php') === false) {
                $errors[] = "Missing semicolon on line " . ($lineNum + 1) . ".";
                break;
            }
        }
    }
    
    if (empty($errors)) {
        return "No obvious errors detected! Your code syntax looks correct. If you're experiencing issues, check for logical errors or runtime exceptions.";
    }
    
    return "Potential issues found: " . implode(" ", $errors);
}

function explainCodeOffline($code, $language) {
    $explanation = "Let me explain your $language code:\n\n";
    
    if ($language === 'javascript') {
        if (strpos($code, 'function') !== false) {
            $explanation .= "• You have function definitions that can be called to execute specific tasks.\n";
        }
        if (strpos($code, 'console.log') !== false) {
            $explanation .= "• console.log() statements will output values to the browser console for debugging.\n";
        }
        if (strpos($code, 'if') !== false) {
            $explanation .= "• Conditional statements (if/else) control the flow of your program based on conditions.\n";
        }
        if (strpos($code, 'for') !== false || strpos($code, 'while') !== false) {
            $explanation .= "• Loops repeat code execution until certain conditions are met.\n";
        }
        if (preg_match('/(?:var|let|const)\s+\w+\s*=/', $code)) {
            $explanation .= "• Variables store data that can be used throughout your program.\n";
        }
    }
    
    if ($language === 'php') {
        if (strpos($code, 'echo') !== false || strpos($code, 'print') !== false) {
            $explanation .= "• Output statements display content to the web page.\n";
        }
        if (strpos($code, '$_GET') !== false || strpos($code, '$_POST') !== false) {
            $explanation .= "• Superglobal variables capture data from HTTP requests.\n";
        }
        if (strpos($code, 'mysql') !== false || strpos($code, 'PDO') !== false) {
            $explanation .= "• Database operations for storing and retrieving data.\n";
        }
    }
    
    $explanation .= "\nThis is a basic analysis. For detailed explanations, consider using AI services or code documentation.";
    
    return $explanation;
}

function getFallbackSuggestion($language) {
    $suggestions = [
        'javascript' => "Consider using 'const' or 'let' instead of 'var', add semicolons after statements, use arrow functions for cleaner code, and handle errors with try-catch blocks.",
        'python' => "Follow PEP 8 style guidelines, use meaningful variable names, add docstrings to functions, and consider using list comprehensions for better readability.",
        'php' => "Use prepared statements for database queries, validate user input, follow PSR coding standards, and use proper error handling with try-catch blocks.",
        'html' => "Use semantic HTML5 elements, add alt attributes to images, ensure proper document structure, and validate your HTML markup.",
        'css' => "Use CSS Grid or Flexbox for layouts, organize your styles logically, use CSS variables for consistency, and consider mobile-first responsive design."
    ];

    return $suggestions[$language] ?? "Write clean, readable code with proper indentation, meaningful variable names, and appropriate comments.";
}
?>