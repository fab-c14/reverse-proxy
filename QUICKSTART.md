# Quick Start Guide

## 1. Upload Files to Your Server

Upload these files to your web server (e.g., `/var/www/html/chatgpt-proxy/`):
- `index.php` - Main entry point
- `ChatGPTProxy.php` - Proxy logic
- `.htaccess` - Apache configuration (optional, for Apache servers)

## 2. Get Your Cookies

1. Visit https://chat.openai.com and log in
2. Press **F12** to open Developer Tools
3. Go to **Application** tab → **Cookies** → **https://chat.openai.com**
4. Copy these three cookie values:
   - `__Secure-next-auth.session-token`
   - `__Secure-next-auth.callback-url`
   - `cf_clearance`

## 3. Test the Proxy (HTTPS)

Replace the placeholder values and run:

```bash
curl -v "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json"
```

**What to look for:**
- `* SSL connection using TLSv1.3` ✓ (HTTPS confirmed)
- HTTP 200 response code ✓
- JSON response with session data ✓

## 4. Use in Your Application

### JavaScript Example:
```javascript
fetch('https://yourserver.com/chatgpt-proxy/api/auth/session', {
    credentials: 'include',
    headers: {
        'Cookie': '__Secure-next-auth.session-token=TOKEN; ...'
    }
})
.then(res => res.json())
.then(data => console.log(data));
```

### PHP Example:
```php
$ch = curl_init('https://yourserver.com/chatgpt-proxy/api/auth/session');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, '__Secure-next-auth.session-token=TOKEN; ...');
$response = curl_exec($ch);
curl_close($ch);
```

## Common Endpoints

- `/api/auth/session` - Check session status
- `/backend-api/models` - Get available models
- `/backend-api/conversations` - List conversations
- `/backend-api/conversation` - Send/receive messages

## Troubleshooting

**"Missing required cookies"** → Provide all three required cookies

**"cURL error"** → Check PHP cURL extension: `php -m | grep curl`

**Empty response** → Cookies may be expired, get fresh ones

**Connection timeout** → Check firewall allows outbound HTTPS to chat.openai.com

## Full Documentation

See [README.md](README.md) for complete documentation and examples.
