<?php
/**
 * ChatGPT Interaction Demo
 * 
 * This script demonstrates how to interact with ChatGPT through the reverse proxy.
 * It sends a prompt to ChatGPT and displays the response.
 * 
 * Usage:
 * 1. Update the cookies below with your actual ChatGPT session cookies
 * 2. Run: php demo-gpt-interaction.php
 */

// ============================================
// CONFIGURATION - UPDATE THESE VALUES
// ============================================

// Your ChatGPT session cookies
$COOKIES = [
    '__Secure-next-auth.session-token' => 'YOUR_SESSION_TOKEN_HERE',
    '__Secure-next-auth.callback-url' => 'https%3A%2F%2Fchat.openai.com',
    'cf_clearance' => 'YOUR_CF_CLEARANCE_HERE'
];

// Proxy URL (for local testing)
$PROXY_URL = 'http://localhost/reverse-proxy';

// The prompt to send to ChatGPT
$PROMPT = "Hello! Please explain what a reverse proxy is in simple terms.";

// ============================================
// FUNCTIONS
// ============================================

/**
 * Build cookie string from array
 */
function buildCookieString($cookies) {
    $parts = [];
    foreach ($cookies as $name => $value) {
        $parts[] = "$name=$value";
    }
    return implode('; ', $parts);
}

/**
 * Make HTTP request to proxy
 */
function makeRequest($url, $cookies, $method = 'GET', $body = null) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CUSTOMREQUEST => $method
    ]);
    
    // Build headers
    $headers = [
        'Cookie: ' . buildCookieString($cookies),
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ];
    
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($response === false) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }
    
    return [
        'success' => true,
        'http_code' => $httpCode,
        'body' => $response
    ];
}

/**
 * Print formatted message
 */
function printMsg($message, $type = 'info') {
    $colors = [
        'info' => "\033[36m",    // Cyan
        'success' => "\033[32m", // Green
        'error' => "\033[31m",   // Red
        'warning' => "\033[33m", // Yellow
        'reset' => "\033[0m"
    ];
    
    echo $colors[$type] . $message . $colors['reset'] . "\n";
}

// ============================================
// MAIN DEMO EXECUTION
// ============================================

echo "\n";
printMsg("==========================================================", 'info');
printMsg("      ChatGPT Reverse Proxy - Interaction Demo", 'info');
printMsg("==========================================================", 'info');
echo "\n";

// Step 1: Validate configuration
printMsg("Step 1: Validating configuration...", 'info');
$valid = true;
foreach (['__Secure-next-auth.session-token', '__Secure-next-auth.callback-url', 'cf_clearance'] as $cookie) {
    if (!isset($COOKIES[$cookie]) || empty($COOKIES[$cookie]) || strpos($COOKIES[$cookie], 'YOUR_') === 0) {
        printMsg("  ✗ Missing or unconfigured: $cookie", 'error');
        $valid = false;
    } else {
        printMsg("  ✓ Cookie configured: $cookie", 'success');
    }
}

if (!$valid) {
    echo "\n";
    printMsg("ERROR: Please update the cookies at the top of this file!", 'error');
    printMsg("To get cookies:", 'warning');
    printMsg("  1. Log in to https://chat.openai.com", 'warning');
    printMsg("  2. Press F12 → Application → Cookies", 'warning');
    printMsg("  3. Copy the three required cookie values", 'warning');
    echo "\n";
    exit(1);
}

echo "\n";

// Step 2: Check session status
printMsg("Step 2: Checking session status...", 'info');
$sessionUrl = rtrim($PROXY_URL, '/') . '/api/auth/session';
$result = makeRequest($sessionUrl, $COOKIES);

if (!$result['success']) {
    printMsg("  ✗ Failed to connect: " . $result['error'], 'error');
    printMsg("\nMake sure:", 'warning');
    printMsg("  - Your web server is running", 'warning');
    printMsg("  - The proxy files are in the correct location", 'warning');
    printMsg("  - The PROXY_URL is correct", 'warning');
    exit(1);
}

if ($result['http_code'] !== 200) {
    printMsg("  ✗ Session check failed (HTTP {$result['http_code']})", 'error');
    printMsg("  Response: " . substr($result['body'], 0, 200), 'error');
    exit(1);
}

$sessionData = json_decode($result['body'], true);
if (isset($sessionData['user'])) {
    printMsg("  ✓ Session valid! Logged in as: " . ($sessionData['user']['email'] ?? 'Unknown'), 'success');
} else {
    printMsg("  ✓ Session endpoint responded", 'success');
}

echo "\n";

// Step 3: Send prompt to ChatGPT
printMsg("Step 3: Sending prompt to ChatGPT...", 'info');
printMsg("  Prompt: \"$PROMPT\"", 'info');
echo "\n";

// Note: The actual conversation endpoint requires a more complex payload
// This is a simplified demonstration showing the structure
$conversationUrl = rtrim($PROXY_URL, '/') . '/backend-api/conversation';

// Build the conversation request payload
$payload = [
    'action' => 'next',
    'messages' => [
        [
            'id' => uniqid(),
            'author' => ['role' => 'user'],
            'content' => [
                'content_type' => 'text',
                'parts' => [$PROMPT]
            ]
        ]
    ],
    'model' => 'text-davinci-002-render-sha',
    'parent_message_id' => '00000000-0000-0000-0000-000000000000'
];

printMsg("  Sending request to ChatGPT...", 'info');
$result = makeRequest($conversationUrl, $COOKIES, 'POST', json_encode($payload));

if (!$result['success']) {
    printMsg("  ✗ Failed to send message: " . $result['error'], 'error');
    exit(1);
}

printMsg("  ✓ Request sent! (HTTP {$result['http_code']})", 'success');
echo "\n";

// Step 4: Display response
printMsg("Step 4: ChatGPT Response:", 'info');
printMsg("----------------------------------------------------------", 'info');

// The response is typically in streaming format (server-sent events)
// For this demo, we'll display the raw response
if ($result['http_code'] === 200) {
    $response = $result['body'];
    
    // Try to parse if it's JSON
    $jsonData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        // Display raw response (could be streaming data)
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            if (strpos($line, 'data: ') === 0) {
                $data = substr($line, 6);
                if ($data === '[DONE]') continue;
                
                $json = json_decode($data, true);
                if ($json && isset($json['message']['content']['parts'][0])) {
                    echo $json['message']['content']['parts'][0];
                }
            }
        }
        echo "\n";
    }
} else {
    printMsg("Response code: " . $result['http_code'], 'warning');
    echo substr($result['body'], 0, 500) . "\n";
}

printMsg("----------------------------------------------------------", 'info');
echo "\n";

// Summary
printMsg("==========================================================", 'success');
printMsg("       Demonstration Complete!", 'success');
printMsg("==========================================================", 'success');
echo "\n";

printMsg("This demo showed:", 'info');
printMsg("  1. ✓ How to configure cookies for authentication", 'success');
printMsg("  2. ✓ How to verify session status through the proxy", 'success');
printMsg("  3. ✓ How to send a prompt to ChatGPT", 'success');
printMsg("  4. ✓ How to receive and display the response", 'success');
echo "\n";

printMsg("You can now use this pattern in your own applications!", 'info');
printMsg("Modify the \$PROMPT variable to ask different questions.", 'info');
echo "\n";
