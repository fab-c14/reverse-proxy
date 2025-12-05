# ChatGPT Reverse Proxy

A lightweight PHP reverse proxy for ChatGPT's web interface. No API keys required - uses session cookies for authentication.

## Features

- ✅ Proxies requests to ChatGPT web interface
- ✅ Cookie-based authentication
- ✅ HTTPS communication
- ✅ Maintains conversation state
- ✅ Minimal dependencies

## Requirements

- PHP 7.4 or higher
- PHP cURL extension
- Web server (Apache/Nginx)
- Valid ChatGPT session cookies

## Required Cookies

Get these from your browser's Developer Tools (F12 → Application → Cookies):

1. `__Secure-next-auth.session-token` - Authentication token
2. `__Secure-next-auth.callback-url` - Callback URL
3. `cf_clearance` - Cloudflare clearance

## Quick Start

### 1. Upload Files

Upload these files to your web server:
```
/var/www/html/chatgpt-proxy/
├── index.php
├── ChatGPTProxy.php
└── .htaccess
```

### 2. Configure Web Server

**Apache**: Use the included `.htaccess` file

**Nginx**: Add to your server block:
```nginx
location /chatgpt-proxy/ {
    try_files $uri $uri/ /chatgpt-proxy/index.php?path=$uri&$args;
}
```

### 3. Get Your Cookies

1. Log in to [chat.openai.com](https://chat.openai.com)
2. Press F12 → Application → Cookies → https://chat.openai.com
3. Copy the three required cookie values

## Demo: Interacting with ChatGPT

Run the included demonstration script to see how to interact with ChatGPT:

```bash
# 1. Edit demo-gpt-interaction.php and add your cookies
# 2. Run the demo
php demo-gpt-interaction.php
```

This demo shows:
- ✓ How to authenticate with cookies
- ✓ How to verify session status
- ✓ How to send prompts to ChatGPT
- ✓ How to receive and display responses

## Usage Examples

### cURL Example

```bash
# Check session status
curl -X GET "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json"
```

### PHP Example

```php
$cookies = [
    '__Secure-next-auth.session-token' => 'YOUR_TOKEN',
    '__Secure-next-auth.callback-url' => 'https%3A%2F%2Fchat.openai.com',
    'cf_clearance' => 'YOUR_CLEARANCE'
];

$cookieString = implode('; ', array_map(
    fn($k, $v) => "$k=$v",
    array_keys($cookies),
    $cookies
));

$ch = curl_init('https://yourserver.com/chatgpt-proxy/api/auth/session');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, $cookieString);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

### JavaScript Example

```javascript
fetch('https://yourserver.com/chatgpt-proxy/api/auth/session', {
    credentials: 'include',
    headers: {
        'Cookie': 'your-cookie-string',
        'Accept': 'application/json'
    }
})
.then(res => res.json())
.then(data => console.log(data));
```

## Common Endpoints

- `/api/auth/session` - Check session status
- `/backend-api/models` - Get available models
- `/backend-api/conversations` - List conversations
- `/backend-api/conversation` - Send/receive messages

## Troubleshooting

**"Missing required cookies"**
→ Provide all three required cookies

**"cURL error"**
→ Check PHP cURL extension: `php -m | grep curl`

**Empty response**
→ Cookies may be expired, get fresh ones

**Connection timeout**
→ Check firewall allows outbound HTTPS to chat.openai.com

## Security Notes

⚠️ **Important:**

1. **Protect cookies** - Treat them like passwords
2. **Use HTTPS** - Always use HTTPS in production
3. **Access control** - Implement authentication on the proxy
4. **Rate limiting** - Consider adding rate limiting

## License

Provided as-is for educational purposes. Ensure your use complies with OpenAI's Terms of Service.
