# API Testing Guide for Localhost/XAMPP

This directory contains test files to help you test the ChatGPT reverse proxy on your localhost using XAMPP.

## Available Test Files

### 1. `test-api.php` - Interactive Web Interface
A beautiful, user-friendly HTML/PHP test interface that runs in your browser.

**Features:**
- 🎨 Modern, responsive UI
- 🔑 Cookie management with localStorage
- 🚀 Quick tests for common endpoints
- 🔧 Custom request builder
- 📊 Formatted JSON responses
- 💾 Save/load cookies for convenience

**How to use:**
1. Make sure XAMPP is running
2. Open in browser: `http://localhost/reverse-proxy/test-api.php`
3. Paste your ChatGPT cookies from browser
4. Click on quick tests or build custom requests
5. View results in real-time

### 2. `test-proxy-cli.php` - Command Line Interface
A PHP script for testing from the command line with colored output.

**Features:**
- ✅ Automated test suite
- 🎨 Colored terminal output
- 📋 Comprehensive test summary
- ⚡ Quick validation checks
- 🔍 Detailed error messages

**How to use:**
1. Open the file in a text editor
2. Update the cookies at the top of the file:
   ```php
   $COOKIES = [
       '__Secure-next-auth.session-token' => 'YOUR_SESSION_TOKEN_HERE',
       '__Secure-next-auth.callback-url' => 'https%3A%2F%2Fchat.openai.com',
       'cf_clearance' => 'YOUR_CF_CLEARANCE_HERE'
   ];
   ```
3. Update the proxy URL if needed:
   ```php
   $PROXY_URL = 'http://localhost/reverse-proxy';
   ```
4. Run from command line:
   ```bash
   php test-proxy-cli.php
   ```

## Getting Your Cookies

Before using any test file, you need to get your ChatGPT session cookies:

### Step-by-step Instructions:

1. **Log in to ChatGPT**
   - Go to [https://chat.openai.com](https://chat.openai.com)
   - Sign in with your account

2. **Open Developer Tools**
   - Press `F12` (Windows/Linux) or `Cmd+Option+I` (Mac)
   - Or right-click → "Inspect"

3. **Navigate to Cookies**
   - Click on the **Application** tab (Chrome) or **Storage** tab (Firefox)
   - In the left sidebar, expand **Cookies**
   - Click on **https://chat.openai.com**

4. **Copy Required Cookies**
   You need these three cookies:
   
   - **`__Secure-next-auth.session-token`**
     - This is your authentication token
     - Usually very long (hundreds of characters)
   
   - **`__Secure-next-auth.callback-url`**
     - Usually: `https%3A%2F%2Fchat.openai.com`
     - This is URL-encoded: `https://chat.openai.com`
   
   - **`cf_clearance`**
     - This is the Cloudflare clearance token
     - Also quite long

5. **Copy Each Value**
   - Click on each cookie name
   - Copy the entire **Value** field
   - Paste into the test file

### Visual Guide:

```
Developer Tools → Application Tab
└── Cookies
    └── https://chat.openai.com
        ├── __Secure-next-auth.session-token ← Copy this value
        ├── __Secure-next-auth.callback-url  ← Copy this value
        └── cf_clearance                      ← Copy this value
```

## XAMPP Setup

### Installation

1. **Download XAMPP**
   - Visit [https://www.apachefriends.org](https://www.apachefriends.org)
   - Download for your operating system (Windows/Mac/Linux)
   - Install XAMPP

2. **Start Services**
   - Open XAMPP Control Panel
   - Start **Apache** (required)
   - PHP should be enabled by default with Apache

3. **Place Files**
   - Navigate to XAMPP's `htdocs` directory:
     - Windows: `C:\xampp\htdocs\`
     - Mac: `/Applications/XAMPP/htdocs/`
     - Linux: `/opt/lampp/htdocs/`
   
   - Create a folder for the proxy (e.g., `reverse-proxy`)
   - Copy all proxy files into this folder:
     ```
     htdocs/
     └── reverse-proxy/
         ├── index.php
         ├── ChatGPTProxy.php
         ├── .htaccess
         ├── test-api.php      ← Test files
         └── test-proxy-cli.php ← Test files
     ```

4. **Verify PHP Installation**
   ```bash
   php -v
   ```
   Should show PHP 7.4 or higher

5. **Check cURL Extension**
   ```bash
   php -m | grep curl
   ```
   Should show `curl` if installed

### Common Issues

#### Apache Won't Start
- **Problem:** Port 80 is already in use
- **Solution:** 
  - Check if another web server is running (IIS, nginx)
  - Change Apache port in XAMPP config
  - Stop conflicting services

#### PHP Not Working
- **Problem:** PHP files download instead of executing
- **Solution:**
  - Make sure Apache is running
  - Restart Apache after configuration changes
  - Check that `.htaccess` is present

#### "Missing required cookies" Error
- **Problem:** Cookies are not configured or expired
- **Solution:**
  - Get fresh cookies from chat.openai.com
  - Make sure you copied the entire cookie value
  - Check for extra spaces or line breaks

## Test Endpoints

The test files include tests for these endpoints:

### 1. Session Check
```
GET /api/auth/session
```
Verifies authentication and returns session information.

**Expected Response:**
```json
{
  "user": {
    "id": "user-xxx",
    "name": "Your Name",
    "email": "your@email.com"
  }
}
```

### 2. Get Models
```
GET /backend-api/models
```
Returns available ChatGPT models.

**Expected Response:**
```json
{
  "models": [
    {
      "slug": "gpt-4",
      "title": "GPT-4"
    }
  ]
}
```

### 3. List Conversations
```
GET /backend-api/conversations?offset=0&limit=20
```
Returns your recent conversations.

**Expected Response:**
```json
{
  "items": [
    {
      "id": "conv-xxx",
      "title": "Conversation Title"
    }
  ]
}
```

### 4. Check Account
```
GET /backend-api/accounts/check
```
Returns account information.

## Troubleshooting

### Connection Errors

**Error:** `Failed to connect to localhost`
- **Cause:** XAMPP Apache is not running
- **Fix:** Start Apache in XAMPP Control Panel

**Error:** `404 Not Found`
- **Cause:** Incorrect URL or files not in correct location
- **Fix:** Verify files are in `htdocs/reverse-proxy/` and URL matches

**Error:** `500 Internal Server Error`
- **Cause:** PHP error in the proxy code
- **Fix:** Check Apache error logs in XAMPP

### Cookie Errors

**Error:** `Missing required cookies`
- **Cause:** Cookies not provided or incomplete
- **Fix:** Make sure all three cookies are configured

**Error:** `401 Unauthorized`
- **Cause:** Cookies have expired or are invalid
- **Fix:** Get fresh cookies from chat.openai.com

**Error:** Empty or error response from ChatGPT
- **Cause:** Cookies expired, CloudFlare blocking, or invalid User-Agent
- **Fix:** 
  - Get fresh cookies
  - Include all optional cookies if available
  - Verify User-Agent header is set

### Testing Issues

**Issue:** Tests hang or timeout
- **Cause:** Network issues or slow response from ChatGPT
- **Fix:** Increase timeout values, check internet connection

**Issue:** CORS errors in browser
- **Cause:** Same-origin policy restrictions
- **Fix:** Use the proxy from the same domain or configure CORS headers

## Advanced Testing

### Using cURL Directly

Test without the HTML interface:

```bash
# Test session
curl "http://localhost/reverse-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json"

# Test models
curl "http://localhost/reverse-proxy/backend-api/models" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json"
```

### Using Postman

1. Create a new request
2. Set URL: `http://localhost/reverse-proxy/api/auth/session`
3. Add headers:
   - `Cookie`: Your cookie string
   - `Accept`: `application/json`
4. Send request

### Using JavaScript/Fetch

```javascript
fetch('http://localhost/reverse-proxy/api/auth/session', {
    method: 'GET',
    headers: {
        'Cookie': 'your-cookie-string',
        'Accept': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

## Security Notes

⚠️ **Important:**

1. **Never commit cookies to version control**
   - Cookies are like passwords
   - Keep them private

2. **Use HTTPS in production**
   - These test files are for localhost only
   - Production should use HTTPS

3. **Cookies expire**
   - Session tokens expire after some time
   - Get fresh cookies when tests fail

4. **Local testing only**
   - These test files are for development
   - Not suitable for production use

## Next Steps

After successful testing:

1. ✅ Verify all tests pass
2. ✅ Understand the API structure
3. ✅ Build your application using the proxy
4. ✅ Implement proper error handling
5. ✅ Add rate limiting for production
6. ✅ Deploy to a server with HTTPS

## Support

If you encounter issues:

1. Check XAMPP logs in `xampp/apache/logs/error.log`
2. Verify PHP version: `php -v` (need 7.4+)
3. Check cURL is installed: `php -m | grep curl`
4. Ensure cookies are fresh and complete
5. Verify proxy URL matches your setup

## Additional Resources

- Main README: [../README.md](../README.md)
- Quick Start Guide: [../QUICKSTART.md](../QUICKSTART.md)
- Example cURL commands: [../examples.sh](../examples.sh)
- ChatGPT: [https://chat.openai.com](https://chat.openai.com)
- XAMPP Documentation: [https://www.apachefriends.org](https://www.apachefriends.org)
