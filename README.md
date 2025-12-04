# ChatGPT Web Interface Reverse Proxy

A lightweight PHP reverse proxy that forwards requests to ChatGPT's web interface (chat.openai.com) without using the OpenAI API. All authentication is handled through session cookies, and all traffic is relayed over HTTPS.

## Features

- ✅ Proxies requests to ChatGPT web interface (NOT the API)
- ✅ Cookie-based authentication (no API keys required)
- ✅ HTTPS-only communication
- ✅ Maintains conversation state
- ✅ Self-contained with minimal dependencies
- ✅ Compatible with standard LAMP stack

## Requirements

- PHP 7.4 or higher
- PHP cURL extension enabled
- HTTPS-enabled web server (Apache/Nginx)
- Valid ChatGPT session cookies

## Required Cookies

The proxy requires the following authentication cookies from your ChatGPT session:

1. **`__Secure-next-auth.session-token`** - Primary authentication token
2. **`__Secure-next-auth.callback-url`** - Callback URL for authentication
3. **`cf_clearance`** - Cloudflare clearance cookie

### Optional Cookies (may improve compatibility)

- `__cf_bm` - Cloudflare bot management
- `_cfuvid` - Cloudflare unique visitor ID
- `__Host-next-auth.csrf-token` - CSRF protection token
- `ajs_anonymous_id` - Analytics anonymous ID
- `oai-did` - OpenAI device ID
- `oai-dm-tgt-c-240329` - OpenAI device management target
- `intercom-id-dgkjq2bp` - Intercom user ID
- `intercom-session-dgkjq2bp` - Intercom session
- `intercom-device-id-dgkjq2bp` - Intercom device ID

## Installation

### 1. Upload Files

Upload the following files to your web server:

```
/var/www/html/chatgpt-proxy/
├── index.php
└── ChatGPTProxy.php
```

### 2. Configure Web Server

**For Apache (.htaccess):**

Create a `.htaccess` file in the proxy directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /chatgpt-proxy/
    
    # Forward all requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?path=$1 [QSA,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

**For Nginx:**

Add to your server block:

```nginx
location /chatgpt-proxy/ {
    try_files $uri $uri/ /chatgpt-proxy/index.php?path=$uri&$args;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. Set Permissions

```bash
chmod 644 index.php ChatGPTProxy.php
chown www-data:www-data index.php ChatGPTProxy.php
```

## Obtaining ChatGPT Session Cookies

1. Log in to [ChatGPT](https://chat.openai.com) in your browser
2. Open Developer Tools (F12)
3. Go to the "Application" or "Storage" tab
4. Navigate to "Cookies" → "https://chat.openai.com"
5. Copy the values of the required cookies listed above

## Usage

### Method 1: Using HTTP Cookies (Browser)

Set the required cookies in your application and make requests to the proxy:

```javascript
// JavaScript example
fetch('https://yourserver.com/chatgpt-proxy/api/auth/session', {
    credentials: 'include', // Include cookies
    headers: {
        'Accept': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

### Method 2: Using Custom Header (cURL)

Pass cookies via the `X-ChatGPT-Cookies` header:

```bash
curl -X GET "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "X-ChatGPT-Cookies: __Secure-next-auth.session-token=YOUR_SESSION_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK_URL; cf_clearance=YOUR_CF_CLEARANCE" \
  -H "Accept: application/json" \
  --insecure
```

### Method 3: Using Standard Cookie Header

```bash
curl -X GET "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_SESSION_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK_URL; cf_clearance=YOUR_CF_CLEARANCE" \
  -H "Accept: application/json" \
  -v
```

## cURL Examples

### 1. Check Session Status (HTTPS)

```bash
curl -k -v -X GET "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN_HERE; __Secure-next-auth.callback-url=https%3A%2F%2Fchat.openai.com; cf_clearance=YOUR_CF_CLEARANCE_HERE" \
  -H "Accept: application/json"
```

**Expected output:** JSON response with session information

### 2. Access ChatGPT Home Page

```bash
curl -k -X GET "https://yourserver.com/chatgpt-proxy/" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
```

**Expected output:** HTML content of ChatGPT interface

### 3. Start a New Conversation (POST)

```bash
curl -k -v -X POST "https://yourserver.com/chatgpt-proxy/backend-api/conversation" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "action": "next",
    "messages": [
      {
        "role": "user",
        "content": {
          "content_type": "text",
          "parts": ["Hello, ChatGPT!"]
        }
      }
    ],
    "model": "text-davinci-002-render-sha",
    "parent_message_id": "00000000-0000-0000-0000-000000000000"
  }'
```

**Expected output:** Streaming response with ChatGPT's reply

### 4. Verify HTTPS Connection

The `-v` (verbose) flag shows the SSL/TLS handshake:

```bash
curl -v "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  2>&1 | grep -E "(SSL|TLS|HTTP)"
```

**Expected output:**
```
* SSL connection using TLSv1.3 / TLS_AES_256_GCM_SHA384
* ALPN, server accepted to use h2
> GET /chatgpt-proxy/api/auth/session HTTP/2
< HTTP/2 200
```

This confirms:
- ✅ HTTPS is being used
- ✅ TLS 1.3 encryption
- ✅ Successful connection

## Testing

### Quick Test Script

Save as `test-proxy.php` and run from command line:

```php
<?php
// Test script for ChatGPT Proxy

// Set your cookies here
$cookies = [
    '__Secure-next-auth.session-token' => 'YOUR_SESSION_TOKEN',
    '__Secure-next-auth.callback-url' => 'https%3A%2F%2Fchat.openai.com',
    'cf_clearance' => 'YOUR_CF_CLEARANCE'
];

// Simulate cookies
foreach ($cookies as $name => $value) {
    $_COOKIE[$name] = $value;
}

// Simulate request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['path'] = 'api/auth/session';

// Load and run proxy
require_once 'ChatGPTProxy.php';
$proxy = new ChatGPTProxy();
$proxy->handleRequest();
```

Run with: `php test-proxy.php`

## Troubleshooting

### Error: "Missing required cookies"

**Solution:** Ensure all three required cookies are provided. Cookies must be fresh and valid.

### Error: "cURL error"

**Solution:** 
- Check that PHP cURL extension is installed: `php -m | grep curl`
- Verify SSL certificates are up to date: `curl-config --ca`
- Check firewall allows outbound HTTPS connections

### Empty or Error Response from ChatGPT

**Solution:**
- Cookies may have expired - obtain fresh cookies from browser
- CloudFlare may be blocking the request - include all optional cookies
- User-Agent may be rejected - ensure a valid browser User-Agent is sent

### Connection Times Out

**Solution:**
- Increase timeout values in `ChatGPTProxy.php` (CURLOPT_TIMEOUT)
- Check server can reach chat.openai.com: `curl -I https://chat.openai.com`

## Security Considerations

⚠️ **Important Security Notes:**

1. **Cookie Protection:** Session cookies are sensitive. Protect them as you would passwords.
2. **HTTPS Only:** Always use HTTPS to prevent cookie theft via man-in-the-middle attacks.
3. **Access Control:** Implement authentication/authorization on the proxy to prevent unauthorized use.
4. **Rate Limiting:** Consider adding rate limiting to prevent abuse.
5. **Cookie Expiration:** Cookies expire after a period of time. Monitor and refresh as needed.

## Architecture

```
[Your Frontend] 
       ↓ HTTPS (with cookies)
[PHP Reverse Proxy (index.php + ChatGPTProxy.php)]
       ↓ HTTPS (with forwarded cookies)
[chat.openai.com Web Interface]
```

## License

This is provided as-is for educational purposes. Ensure your use complies with OpenAI's Terms of Service.

## Verification Checklist

- ✅ No OpenAI API keys are used or referenced
- ✅ All communication uses HTTPS
- ✅ Cookies are forwarded exactly as received
- ✅ Conversation state is maintained through cookie sessions
- ✅ Self-contained with no external dependencies (except cURL)
- ✅ Compatible with standard LAMP stack
- ✅ Single entry point (index.php) with helper class (ChatGPTProxy.php)

## Notes

- This proxy communicates with ChatGPT's **web interface**, not the OpenAI API
- No API keys are required or used anywhere in the code
- Authentication relies entirely on browser session cookies
- The proxy maintains conversation state through the forwarded session cookies
- All traffic between the proxy and ChatGPT uses HTTPS (enforced by the https:// URL scheme)
