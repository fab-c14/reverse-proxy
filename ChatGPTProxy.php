<?php
/**
 * ChatGPTProxy - Reverse proxy for ChatGPT web interface
 * 
 * This class handles proxying requests to chat.openai.com, forwarding
 * authentication cookies and maintaining conversation state.
 */

class ChatGPTProxy
{
    /** @var string Base URL for ChatGPT web interface */
    private const CHATGPT_BASE_URL = 'https://chat.openai.com';
    
    /** @var array Required cookie names for authentication */
    private const REQUIRED_COOKIES = [
        '__Secure-next-auth.session-token',
        '__Secure-next-auth.callback-url',
        'cf_clearance'
    ];
    
    /** @var array Additional cookies that may be present */
    private const OPTIONAL_COOKIES = [
        '__cf_bm',
        '_cfuvid',
        '__Host-next-auth.csrf-token',
        'ajs_anonymous_id',
        'oai-did',
        'oai-dm-tgt-c-240329',  // OpenAI device management cookie (date suffix may vary)
        'intercom-id-dgkjq2bp',
        'intercom-session-dgkjq2bp',
        'intercom-device-id-dgkjq2bp'
    ];
    
    /** @var array HTTP headers to forward from client */
    private array $forwardHeaders = [];
    
    /** @var array Cookies to forward to ChatGPT */
    private array $cookies = [];
    
    /** @var string Target path on ChatGPT server */
    private string $targetPath = '';
    
    /** @var string HTTP method */
    private string $method = 'GET';
    
    /** @var string|null Request body */
    private ?string $requestBody = null;
    
    /**
     * Handle incoming request and proxy it to ChatGPT
     */
    public function handleRequest(): void
    {
        try {
            // Parse the incoming request
            $this->parseRequest();
            
            // Validate required cookies are present
            $this->validateCookies();
            
            // Build target URL
            $targetUrl = $this->buildTargetUrl();
            
            // Forward the request
            $response = $this->forwardRequest($targetUrl);
            
            // Send response back to client
            $this->sendResponse($response);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    /**
     * Parse incoming request details
     */
    private function parseRequest(): void
    {
        // Get HTTP method
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Get target path from query parameter or PATH_INFO
        $this->targetPath = $_GET['path'] ?? $_SERVER['PATH_INFO'] ?? '/';
        
        // Remove leading slash from path if present (we'll add it in buildTargetUrl)
        $this->targetPath = ltrim($this->targetPath, '/');
        
        // Get request body for POST/PUT/PATCH
        if (in_array($this->method, ['POST', 'PUT', 'PATCH'])) {
            $this->requestBody = file_get_contents('php://input');
        }
        
        // Extract cookies from incoming request
        $this->extractCookies();
        
        // Extract headers to forward
        $this->extractHeaders();
    }
    
    /**
     * Extract cookies from the request
     */
    private function extractCookies(): void
    {
        $allCookies = array_merge(self::REQUIRED_COOKIES, self::OPTIONAL_COOKIES);
        
        // First, check $_COOKIE superglobal (populated from browser cookies)
        foreach ($allCookies as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                $this->cookies[$cookieName] = $_COOKIE[$cookieName];
            }
        }
        
        // Also check for cookies in the standard HTTP Cookie header
        // This is important for API clients, cURL, and test scripts
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $headerCookies = $this->parseCookieString($_SERVER['HTTP_COOKIE']);
            $this->cookies = array_merge($this->cookies, $headerCookies);
        }
        
        // Also check for cookies in custom header (X-ChatGPT-Cookies)
        if (isset($_SERVER['HTTP_X_CHATGPT_COOKIES'])) {
            $customCookies = $this->parseCookieString($_SERVER['HTTP_X_CHATGPT_COOKIES']);
            $this->cookies = array_merge($this->cookies, $customCookies);
        }
    }
    
    /**
     * Parse cookie string into associative array
     */
    private function parseCookieString(string $cookieString): array
    {
        $cookies = [];
        $pairs = explode(';', $cookieString);
        
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (empty($pair)) continue;
            
            $parts = explode('=', $pair, 2);
            if (count($parts) === 2) {
                $cookies[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        return $cookies;
    }
    
    /**
     * Extract headers to forward to ChatGPT
     */
    private function extractHeaders(): void
    {
        // Headers to forward
        $headersToForward = [
            'HTTP_CONTENT_TYPE' => 'Content-Type',
            'HTTP_ACCEPT' => 'Accept',
            'HTTP_ACCEPT_LANGUAGE' => 'Accept-Language',
            'HTTP_ACCEPT_ENCODING' => 'Accept-Encoding',
            'HTTP_USER_AGENT' => 'User-Agent',
            'HTTP_REFERER' => 'Referer',
            'HTTP_ORIGIN' => 'Origin',
            'HTTP_X_REQUESTED_WITH' => 'X-Requested-With',
        ];
        
        foreach ($headersToForward as $serverKey => $headerName) {
            if (isset($_SERVER[$serverKey]) && !empty($_SERVER[$serverKey])) {
                $this->forwardHeaders[$headerName] = $_SERVER[$serverKey];
            }
        }
        
        // Set default User-Agent if not provided
        // Using a recent Chrome version for better compatibility
        if (!isset($this->forwardHeaders['User-Agent'])) {
            $this->forwardHeaders['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        }
    }
    
    /**
     * Validate that required cookies are present
     */
    private function validateCookies(): void
    {
        $missingCookies = [];
        
        foreach (self::REQUIRED_COOKIES as $cookieName) {
            if (!isset($this->cookies[$cookieName])) {
                $missingCookies[] = $cookieName;
            }
        }
        
        if (!empty($missingCookies)) {
            throw new Exception(
                'Missing required cookies: ' . implode(', ', $missingCookies) . '. ' .
                'Please provide authentication cookies via Cookie header or X-ChatGPT-Cookies header.'
            );
        }
    }
    
    /**
     * Build target URL for ChatGPT
     */
    private function buildTargetUrl(): string
    {
        $url = self::CHATGPT_BASE_URL;
        
        // Add path
        if (!empty($this->targetPath)) {
            $url .= '/' . $this->targetPath;
        }
        
        // Add query string
        if (!empty($_SERVER['QUERY_STRING'])) {
            // Remove 'path' parameter from query string if present
            parse_str($_SERVER['QUERY_STRING'], $params);
            unset($params['path']);
            
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
        }
        
        return $url;
    }
    
    /**
     * Forward request to ChatGPT using cURL
     */
    private function forwardRequest(string $url): array
    {
        $ch = curl_init();
        
        // Basic cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_ENCODING => '', // Accept all encodings
        ]);
        
        // Set HTTP method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        
        // Set request body for POST/PUT/PATCH
        if ($this->requestBody !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
        }
        
        // Build and set headers
        $headers = $this->buildRequestHeaders();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Execute request
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new Exception("cURL error ({$errno}): {$error}");
        }
        
        // Get response info
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        // Split headers and body
        $headerStr = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        return [
            'code' => $httpCode,
            'headers' => $this->parseResponseHeaders($headerStr),
            'body' => $body
        ];
    }
    
    /**
     * Build request headers for cURL
     */
    private function buildRequestHeaders(): array
    {
        $headers = [];
        
        // Add forwarded headers
        foreach ($this->forwardHeaders as $name => $value) {
            $headers[] = "{$name}: {$value}";
        }
        
        // Add cookies
        if (!empty($this->cookies)) {
            $cookieString = $this->buildCookieString();
            $headers[] = "Cookie: {$cookieString}";
        }
        
        // Add host header
        $headers[] = 'Host: chat.openai.com';
        
        return $headers;
    }
    
    /**
     * Build cookie string from cookies array
     */
    private function buildCookieString(): string
    {
        $parts = [];
        foreach ($this->cookies as $name => $value) {
            $parts[] = "{$name}={$value}";
        }
        return implode('; ', $parts);
    }
    
    /**
     * Parse response headers
     */
    private function parseResponseHeaders(string $headerStr): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerStr);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(':', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Store multiple values for same header name (like Set-Cookie)
                if (isset($headers[$name])) {
                    if (!is_array($headers[$name])) {
                        $headers[$name] = [$headers[$name]];
                    }
                    $headers[$name][] = $value;
                } else {
                    $headers[$name] = $value;
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Send response back to client
     */
    private function sendResponse(array $response): void
    {
        // Set HTTP response code
        http_response_code($response['code']);
        
        // Forward response headers (skip some headers)
        $skipHeaders = ['transfer-encoding', 'connection', 'content-encoding'];
        
        foreach ($response['headers'] as $name => $value) {
            $nameLower = strtolower($name);
            
            if (in_array($nameLower, $skipHeaders)) {
                continue;
            }
            
            // Handle multiple header values
            if (is_array($value)) {
                foreach ($value as $v) {
                    header("{$name}: {$v}", false);
                }
            } else {
                header("{$name}: {$value}");
            }
        }
        
        // Send body
        echo $response['body'];
    }
    
    /**
     * Send error response
     */
    private function sendError(string $message, int $code = 500): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * Get list of required cookie names
     */
    public static function getRequiredCookies(): array
    {
        return self::REQUIRED_COOKIES;
    }
    
    /**
     * Get list of optional cookie names
     */
    public static function getOptionalCookies(): array
    {
        return self::OPTIONAL_COOKIES;
    }
}
