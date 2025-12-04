# Testing Guide for ChatGPT Reverse Proxy

This guide explains how to use `test.js` to easily test your reverse proxy setup.

## Quick Start

### 1. Prerequisites

- Node.js 18 or higher (for native fetch API)
- A running instance of the ChatGPT reverse proxy
- Valid ChatGPT session cookies

### 2. Get Your Cookies

1. Log in to [https://chat.openai.com](https://chat.openai.com) in your browser
2. Press **F12** to open Developer Tools
3. Go to **Application** (Chrome) or **Storage** (Firefox) tab
4. Navigate to **Cookies** → **https://chat.openai.com**
5. Copy the following cookie values:
   - `__Secure-next-auth.session-token`
   - `__Secure-next-auth.callback-url` (usually `https%3A%2F%2Fchat.openai.com`)
   - `cf_clearance`

### 3. Configure the Test Script

Open `test.js` and update the `CONFIG` section at the top:

```javascript
const CONFIG = {
  // Your proxy URL
  PROXY_URL: 'https://yourserver.com/chatgpt-proxy',
  
  // Your ChatGPT cookies
  COOKIES: {
    '__Secure-next-auth.session-token': 'YOUR_SESSION_TOKEN_HERE',
    '__Secure-next-auth.callback-url': 'https%3A%2F%2Fchat.openai.com',
    'cf_clearance': 'YOUR_CF_CLEARANCE_HERE'
  },
  
  VERBOSE: true, // Set to false for less output
  TIMEOUT: 30000, // Request timeout in milliseconds
};
```

### 4. Run the Tests

```bash
# Make the script executable (Linux/Mac)
chmod +x test.js

# Run the tests
node test.js

# Or run directly (if executable)
./test.js
```

## What Gets Tested

The test suite includes the following tests:

1. **Session Status** - Verifies that your cookies are valid and the session is active
2. **Get Models** - Tests retrieving available ChatGPT models
3. **Get Conversations** - Tests retrieving conversation list
4. **Error Handling** - Verifies that the proxy correctly handles missing cookies
5. **Custom Header Method** - Tests the alternative `X-ChatGPT-Cookies` header method
6. **HTTPS Check** - Verifies that you're using HTTPS (recommended)

## Example Output

```
ChatGPT Reverse Proxy - Test Suite
Starting tests...

======================================================================
Configuration Validation
======================================================================
✓ Configuration looks valid
  Proxy URL: https://yourserver.com/chatgpt-proxy
  Session Token: eyJhbGciOiJkaXIiLCJ...

======================================================================
Running Tests
======================================================================
→ GET https://yourserver.com/chatgpt-proxy/api/auth/session
✓ Session Status
  Session is valid and active
  Response: { "user": { "id": "...", "name": "...", ... } }

→ GET https://yourserver.com/chatgpt-proxy/backend-api/models
✓ Get Models
  Retrieved 5 models
  Response: { "models": [...] }

→ GET https://yourserver.com/chatgpt-proxy/backend-api/conversations?offset=0&limit=20
✓ Get Conversations
  Retrieved 10 conversations
  Response: { "items": [...] }

→ GET https://yourserver.com/chatgpt-proxy/api/auth/session (without cookies)
✓ Error Handling
  Proxy correctly returns error for missing cookies
  Response: { "error": true, "message": "Missing required cookies..." }

→ GET https://yourserver.com/chatgpt-proxy/api/auth/session (using X-ChatGPT-Cookies header)
✓ Custom Header Method
  X-ChatGPT-Cookies header method works
  Response: { "user": { ... } }

✓ HTTPS Check
  Proxy URL uses HTTPS (recommended)

======================================================================
Test Summary
======================================================================
Total Tests: 6
Passed: 6
Failed: 0
Skipped: 0
Pass Rate: 100.0%

🎉 All tests passed!

======================================================================
```

## Configuration Options

### PROXY_URL

The base URL of your proxy server. Examples:
- `https://yourserver.com/chatgpt-proxy`
- `http://localhost/chatgpt-proxy` (for local testing)
- `https://example.com:8080/proxy` (custom port)

### VERBOSE

- `true` (default): Shows detailed output including request URLs and response data
- `false`: Shows only test results and summaries

### TIMEOUT

Request timeout in milliseconds. Default is 30000 (30 seconds).

Adjust if your proxy or network is slow:
```javascript
TIMEOUT: 60000, // 60 seconds
```

## Troubleshooting

### "Cannot run tests without valid configuration"

**Solution:** Update the `CONFIG` section in `test.js` with your actual proxy URL and cookies.

### "This script requires Node.js 18 or higher"

**Solution:** Upgrade Node.js to version 18 or higher. Check your version with:
```bash
node --version
```

### "Request timeout after 30000ms"

**Solution:** 
- Increase the `TIMEOUT` value in the config
- Check that your proxy server is running and accessible
- Verify your network connection

### All tests fail with "HTTP 500" or similar errors

**Solution:**
- Check that your proxy server is running correctly
- Verify your cookies are valid (they expire periodically)
- Check proxy server logs for errors

### "Missing required cookies" error

**Solution:**
- Make sure you've updated all three required cookies in the config
- Verify the cookie values are correct (no extra spaces or quotes)
- Get fresh cookies from your browser if they've expired

### Tests pass but responses are empty

**Solution:**
- Your cookies may have expired - get fresh cookies from your browser
- CloudFlare may be blocking requests - ensure all cookies are included
- Check that you're logged into ChatGPT in your browser

## Advanced Usage

### Run Specific Tests

You can modify `test.js` to comment out tests you don't want to run:

```javascript
async runAllTests() {
  // ...
  await this.testSessionStatus();
  // await this.testGetModels();  // Skip this test
  await this.testGetConversations();
  // ...
}
```

### Add Custom Tests

You can add your own tests by following the pattern:

```javascript
async testCustomEndpoint() {
  await this.runTest('My Custom Test', async () => {
    const response = await this.makeRequest('/your-custom-endpoint');
    
    if (response.ok) {
      this.printResult('Custom Test', true, 'Success!', response.data);
    } else {
      this.printResult('Custom Test', false, 'Failed', response.data);
    }
  });
}
```

Then add it to `runAllTests()`:

```javascript
await this.testCustomEndpoint();
```

### Use in CI/CD

The script exits with code 0 on success and 1 on fatal errors, making it suitable for CI/CD pipelines:

```bash
#!/bin/bash
node test.js
if [ $? -eq 0 ]; then
  echo "Proxy tests passed"
else
  echo "Proxy tests failed"
  exit 1
fi
```

## See Also

- [README.md](README.md) - Main documentation
- [QUICKSTART.md](QUICKSTART.md) - Quick setup guide
- [examples.sh](examples.sh) - cURL examples for manual testing
