<?php
/**
 * ChatGPT Proxy CLI Test Script
 * 
 * This script tests the ChatGPT reverse proxy from the command line.
 * Usage: php test-proxy-cli.php
 * 
 * Configure your cookies below in the CONFIGURATION section.
 */

// Colors for terminal output
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

// ============================================
// CONFIGURATION - UPDATE THESE VALUES
// ============================================

// Your ChatGPT session cookies (Get these from browser)
$COOKIES = [
    '__Secure-next-auth.session-token' => 'YOUR_SESSION_TOKEN_HERE',
    '__Secure-next-auth.callback-url' => 'https%3A%2F%2Fchat.openai.com',
    'cf_clearance' => 'YOUR_CF_CLEARANCE_HERE'
];

// Your proxy URL (for XAMPP localhost)
$PROXY_URL = 'http://localhost/reverse-proxy';

// ============================================
// TEST FUNCTIONS
// ============================================

/**
 * Print colored message to console
 */
function printMessage($message, $color = Colors::RESET, $bold = false) {
    $output = $bold ? Colors::BOLD : '';
    $output .= $color . $message . Colors::RESET . "\n";
    echo $output;
}

/**
 * Print section header
 */
function printHeader($title) {
    echo "\n";
    printMessage(str_repeat('=', 70), Colors::CYAN, true);
    printMessage($title, Colors::CYAN, true);
    printMessage(str_repeat('=', 70), Colors::CYAN, true);
    echo "\n";
}

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
function makeRequest($proxyUrl, $endpoint, $cookies, $method = 'GET', $body = null) {
    $url = rtrim($proxyUrl, '/') . '/' . ltrim($endpoint, '/');
    
    printMessage("→ Sending $method request to: $url", Colors::BLUE);
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
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
    
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false,
            'error' => $error
        ];
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    curl_close($ch);
    
    $headerStr = substr($response, 0, $headerSize);
    $bodyStr = substr($response, $headerSize);
    
    return [
        'success' => true,
        'status' => $httpCode,
        'headers' => $headerStr,
        'body' => $bodyStr
    ];
}

/**
 * Display test result
 */
function displayResult($result, $testName) {
    if (!$result['success']) {
        printMessage("✗ FAILED: $testName", Colors::RED, true);
        printMessage("Error: " . $result['error'], Colors::RED);
        return false;
    }
    
    $status = $result['status'];
    
    if ($status >= 200 && $status < 300) {
        printMessage("✓ PASSED: $testName", Colors::GREEN, true);
        printMessage("Status: $status", Colors::GREEN);
    } else {
        printMessage("✗ FAILED: $testName", Colors::RED, true);
        printMessage("Status: $status", Colors::RED);
    }
    
    // Try to format JSON response
    $body = $result['body'];
    $json = json_decode($body, true);
    
    if ($json !== null) {
        printMessage("\nResponse (formatted):", Colors::YELLOW);
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        printMessage("\nResponse (raw):", Colors::YELLOW);
        echo substr($body, 0, 500) . (strlen($body) > 500 ? '...' : '') . "\n";
    }
    
    return $status >= 200 && $status < 300;
}

/**
 * Validate configuration
 */
function validateConfig($cookies, $proxyUrl) {
    printHeader("Configuration Validation");
    
    $valid = true;
    
    // Check required cookies
    $requiredCookies = [
        '__Secure-next-auth.session-token',
        '__Secure-next-auth.callback-url',
        'cf_clearance'
    ];
    
    foreach ($requiredCookies as $cookieName) {
        if (!isset($cookies[$cookieName]) || 
            empty($cookies[$cookieName]) || 
            strpos($cookies[$cookieName], 'YOUR_') === 0) {
            printMessage("✗ Missing or unconfigured: $cookieName", Colors::RED);
            $valid = false;
        } else {
            $length = strlen($cookies[$cookieName]);
            printMessage("✓ Found: $cookieName (length: $length)", Colors::GREEN);
        }
    }
    
    // Check proxy URL
    if (empty($proxyUrl) || strpos($proxyUrl, 'localhost') === false) {
        printMessage("✗ Proxy URL not configured for localhost", Colors::RED);
        $valid = false;
    } else {
        printMessage("✓ Proxy URL: $proxyUrl", Colors::GREEN);
    }
    
    return $valid;
}

/**
 * Run all tests
 */
function runTests($proxyUrl, $cookies) {
    $results = [];
    
    // Test 1: Check Session
    printHeader("Test 1: Check Session Status");
    $result = makeRequest($proxyUrl, 'api/auth/session', $cookies);
    $results['session'] = displayResult($result, 'Session Check');
    sleep(1);
    
    // Test 2: Get Models
    printHeader("Test 2: Get Available Models");
    $result = makeRequest($proxyUrl, 'backend-api/models', $cookies);
    $results['models'] = displayResult($result, 'Get Models');
    sleep(1);
    
    // Test 3: List Conversations
    printHeader("Test 3: List Conversations");
    $result = makeRequest($proxyUrl, 'backend-api/conversations?offset=0&limit=5', $cookies);
    $results['conversations'] = displayResult($result, 'List Conversations');
    sleep(1);
    
    // Test 4: Check Accounts
    printHeader("Test 4: Check Account");
    $result = makeRequest($proxyUrl, 'backend-api/accounts/check', $cookies);
    $results['accounts'] = displayResult($result, 'Check Account');
    
    return $results;
}

/**
 * Display summary
 */
function displaySummary($results) {
    printHeader("Test Summary");
    
    $passed = 0;
    $failed = 0;
    
    foreach ($results as $test => $success) {
        if ($success) {
            printMessage("✓ $test", Colors::GREEN);
            $passed++;
        } else {
            printMessage("✗ $test", Colors::RED);
            $failed++;
        }
    }
    
    echo "\n";
    printMessage("Total: " . count($results) . " tests", Colors::CYAN, true);
    printMessage("Passed: $passed", Colors::GREEN, true);
    printMessage("Failed: $failed", Colors::RED, true);
    
    if ($failed === 0) {
        echo "\n";
        printMessage("🎉 All tests passed! Your proxy is working correctly.", Colors::GREEN, true);
    } else {
        echo "\n";
        printMessage("⚠️  Some tests failed. Check configuration and cookies.", Colors::YELLOW, true);
    }
}

/**
 * Display instructions
 */
function displayInstructions() {
    printHeader("ChatGPT Proxy Test Script");
    
    printMessage("This script will test your ChatGPT reverse proxy.", Colors::CYAN);
    echo "\n";
    printMessage("Before running, make sure:", Colors::YELLOW, true);
    printMessage("1. XAMPP is running (Apache + PHP)", Colors::YELLOW);
    printMessage("2. Proxy files are in htdocs (e.g., C:\\xampp\\htdocs\\reverse-proxy)", Colors::YELLOW);
    printMessage("3. You've configured your cookies in this file", Colors::YELLOW);
    printMessage("4. The \$PROXY_URL variable matches your setup", Colors::YELLOW);
    echo "\n";
    printMessage("To get cookies:", Colors::YELLOW, true);
    printMessage("1. Log in to https://chat.openai.com", Colors::YELLOW);
    printMessage("2. Press F12 → Application → Cookies → https://chat.openai.com", Colors::YELLOW);
    printMessage("3. Copy the values and paste them at the top of this file", Colors::YELLOW);
    echo "\n";
}

// ============================================
// MAIN EXECUTION
// ============================================

// Display instructions
displayInstructions();

// Validate configuration
if (!validateConfig($COOKIES, $PROXY_URL)) {
    echo "\n";
    printMessage("❌ Configuration validation failed!", Colors::RED, true);
    printMessage("Please update the cookies and proxy URL at the top of this file.", Colors::YELLOW);
    exit(1);
}

// Ask user to confirm
echo "\n";
printMessage("Press ENTER to start tests, or Ctrl+C to cancel...", Colors::CYAN, true);
if (php_sapi_name() === 'cli') {
    fgets(STDIN);
}

// Run tests
$results = runTests($PROXY_URL, $COOKIES);

// Display summary
displaySummary($results);

echo "\n";
printMessage("Testing complete!", Colors::CYAN, true);
echo "\n";
