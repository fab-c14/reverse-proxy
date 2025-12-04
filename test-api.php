<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatGPT Proxy API Test - Localhost/XAMPP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        button.secondary {
            background: #6c757d;
        }
        
        .result {
            margin-top: 20px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 2px solid #ddd;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .result pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .success {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .info {
            padding: 15px;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #0c5460;
        }
        
        .info h3 {
            margin-bottom: 10px;
            color: #0c5460;
        }
        
        .info ol {
            margin-left: 20px;
        }
        
        .info li {
            margin: 5px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .status-success {
            background: #28a745;
            color: white;
        }
        
        .status-error {
            background: #dc3545;
            color: white;
        }
        
        .status-info {
            background: #17a2b8;
            color: white;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .test-card {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 2px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .test-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .test-card h3 {
            color: #667eea;
            margin-bottom: 8px;
            font-size: 1.1em;
        }
        
        .test-card p {
            color: #666;
            font-size: 0.9em;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 ChatGPT Proxy API Tester</h1>
            <p>Test your reverse proxy on localhost/XAMPP</p>
        </div>
        
        <div class="content">
            <!-- Cookie Configuration Section -->
            <div class="section">
                <h2>🔑 Cookie Configuration</h2>
                <div class="info">
                    <h3>How to get your cookies:</h3>
                    <ol>
                        <li>Log in to <a href="https://chat.openai.com" target="_blank">chat.openai.com</a></li>
                        <li>Press <strong>F12</strong> to open Developer Tools</li>
                        <li>Go to <strong>Application</strong> tab → <strong>Cookies</strong> → <strong>https://chat.openai.com</strong></li>
                        <li>Copy the values of the three required cookies below</li>
                    </ol>
                </div>
                
                <div class="form-group">
                    <label for="sessionToken">__Secure-next-auth.session-token (Required) *</label>
                    <textarea id="sessionToken" placeholder="Paste your session token here"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="callbackUrl">__Secure-next-auth.callback-url (Required) *</label>
                    <input type="text" id="callbackUrl" value="https%3A%2F%2Fchat.openai.com" placeholder="Usually: https%3A%2F%2Fchat.openai.com">
                </div>
                
                <div class="form-group">
                    <label for="cfClearance">cf_clearance (Required) *</label>
                    <textarea id="cfClearance" placeholder="Paste your Cloudflare clearance token here"></textarea>
                </div>
                
                <div class="button-group">
                    <button onclick="saveCookies()">💾 Save Cookies</button>
                    <button class="secondary" onclick="loadCookies()">📂 Load Saved Cookies</button>
                    <button class="secondary" onclick="clearCookies()">🗑️ Clear Cookies</button>
                </div>
            </div>
            
            <!-- Proxy Configuration Section -->
            <div class="section">
                <h2>⚙️ Proxy Configuration</h2>
                <div class="form-group">
                    <label for="proxyUrl">Proxy URL (Localhost/XAMPP)</label>
                    <input type="text" id="proxyUrl" value="http://localhost/reverse-proxy" placeholder="http://localhost/reverse-proxy">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Update this to match your XAMPP directory structure (e.g., http://localhost/chatgpt-proxy)
                    </small>
                </div>
            </div>
            
            <!-- Quick Tests Section -->
            <div class="section">
                <h2>🚀 Quick Tests</h2>
                <div class="grid">
                    <div class="test-card" onclick="testEndpoint('session')">
                        <h3>1. Check Session</h3>
                        <p>Test authentication and verify cookies are working</p>
                    </div>
                    
                    <div class="test-card" onclick="testEndpoint('models')">
                        <h3>2. Get Models</h3>
                        <p>Fetch available ChatGPT models</p>
                    </div>
                    
                    <div class="test-card" onclick="testEndpoint('conversations')">
                        <h3>3. List Conversations</h3>
                        <p>Get your recent conversations</p>
                    </div>
                    
                    <div class="test-card" onclick="testEndpoint('accounts')">
                        <h3>4. Get Accounts</h3>
                        <p>Check account information</p>
                    </div>
                </div>
            </div>
            
            <!-- Custom Request Section -->
            <div class="section">
                <h2>🔧 Custom Request</h2>
                <div class="form-group">
                    <label for="customEndpoint">Endpoint Path</label>
                    <input type="text" id="customEndpoint" placeholder="api/auth/session" value="api/auth/session">
                </div>
                
                <div class="form-group">
                    <label for="requestMethod">HTTP Method</label>
                    <select id="requestMethod">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="requestBody">Request Body (JSON, for POST/PUT/PATCH)</label>
                    <textarea id="requestBody" placeholder='{"key": "value"}'></textarea>
                </div>
                
                <div class="button-group">
                    <button onclick="testCustomRequest()">▶️ Send Custom Request</button>
                    <button class="secondary" onclick="clearResults()">🧹 Clear Results</button>
                </div>
            </div>
            
            <!-- Results Section -->
            <div class="section">
                <h2>📊 Results</h2>
                <div id="results" class="result">
                    <p style="color: #999;">No tests run yet. Click on a test above to get started!</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Cookie management
        function saveCookies() {
            const cookies = {
                sessionToken: document.getElementById('sessionToken').value.trim(),
                callbackUrl: document.getElementById('callbackUrl').value.trim(),
                cfClearance: document.getElementById('cfClearance').value.trim()
            };
            
            if (!cookies.sessionToken || !cookies.callbackUrl || !cookies.cfClearance) {
                showResult('error', 'Please fill in all required cookie fields!');
                return;
            }
            
            localStorage.setItem('chatgpt_cookies', JSON.stringify(cookies));
            showResult('success', '✅ Cookies saved to browser localStorage!');
        }
        
        function loadCookies() {
            const stored = localStorage.getItem('chatgpt_cookies');
            if (!stored) {
                showResult('error', 'No saved cookies found!');
                return;
            }
            
            const cookies = JSON.parse(stored);
            document.getElementById('sessionToken').value = cookies.sessionToken || '';
            document.getElementById('callbackUrl').value = cookies.callbackUrl || '';
            document.getElementById('cfClearance').value = cookies.cfClearance || '';
            
            showResult('success', '✅ Cookies loaded from localStorage!');
        }
        
        function clearCookies() {
            if (confirm('Are you sure you want to clear saved cookies?')) {
                localStorage.removeItem('chatgpt_cookies');
                document.getElementById('sessionToken').value = '';
                document.getElementById('callbackUrl').value = 'https%3A%2F%2Fchat.openai.com';
                document.getElementById('cfClearance').value = '';
                showResult('success', '✅ Cookies cleared!');
            }
        }
        
        // Get cookies from form
        function getCookies() {
            return {
                sessionToken: document.getElementById('sessionToken').value.trim(),
                callbackUrl: document.getElementById('callbackUrl').value.trim(),
                cfClearance: document.getElementById('cfClearance').value.trim()
            };
        }
        
        // Validate cookies
        function validateCookies() {
            const cookies = getCookies();
            if (!cookies.sessionToken || !cookies.callbackUrl || !cookies.cfClearance) {
                showResult('error', '❌ Error: Please configure all required cookies first!');
                return false;
            }
            return true;
        }
        
        // Build cookie string
        function buildCookieString() {
            const cookies = getCookies();
            return `__Secure-next-auth.session-token=${cookies.sessionToken}; __Secure-next-auth.callback-url=${cookies.callbackUrl}; cf_clearance=${cookies.cfClearance}`;
        }
        
        // Test predefined endpoints
        async function testEndpoint(type) {
            if (!validateCookies()) return;
            
            const endpoints = {
                session: { path: 'api/auth/session', method: 'GET' },
                models: { path: 'backend-api/models', method: 'GET' },
                conversations: { path: 'backend-api/conversations?offset=0&limit=20', method: 'GET' },
                accounts: { path: 'backend-api/accounts/check', method: 'GET' }
            };
            
            const config = endpoints[type];
            if (!config) {
                showResult('error', 'Unknown endpoint type!');
                return;
            }
            
            const proxyUrl = document.getElementById('proxyUrl').value.trim();
            const url = `${proxyUrl}/${config.path}`;
            
            showResult('info', `🔄 Testing ${type}...<span class="loading"></span>`);
            
            try {
                const response = await fetch(url, {
                    method: config.method,
                    headers: {
                        'Cookie': buildCookieString(),
                        'Accept': 'application/json',
                        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    },
                    credentials: 'include'
                });
                
                const contentType = response.headers.get('content-type');
                let data;
                
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    data = await response.text();
                }
                
                const result = {
                    endpoint: config.path,
                    method: config.method,
                    status: response.status,
                    statusText: response.statusText,
                    headers: Object.fromEntries(response.headers.entries()),
                    data: data
                };
                
                if (response.ok) {
                    showResult('success', formatResult('✅ Success!', result));
                } else {
                    showResult('error', formatResult('❌ Error!', result));
                }
            } catch (error) {
                showResult('error', `❌ Network Error:\n\n${error.message}\n\nMake sure:\n1. XAMPP is running\n2. The proxy URL is correct\n3. PHP files are in the correct directory`);
            }
        }
        
        // Test custom request
        async function testCustomRequest() {
            if (!validateCookies()) return;
            
            const proxyUrl = document.getElementById('proxyUrl').value.trim();
            const endpoint = document.getElementById('customEndpoint').value.trim();
            const method = document.getElementById('requestMethod').value;
            const body = document.getElementById('requestBody').value.trim();
            
            if (!endpoint) {
                showResult('error', '❌ Please specify an endpoint path!');
                return;
            }
            
            const url = `${proxyUrl}/${endpoint}`;
            
            showResult('info', `🔄 Sending ${method} request...<span class="loading"></span>`);
            
            try {
                const options = {
                    method: method,
                    headers: {
                        'Cookie': buildCookieString(),
                        'Accept': 'application/json',
                        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    },
                    credentials: 'include'
                };
                
                if (body && ['POST', 'PUT', 'PATCH'].includes(method)) {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = body;
                }
                
                const response = await fetch(url, options);
                
                const contentType = response.headers.get('content-type');
                let data;
                
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    data = await response.text();
                }
                
                const result = {
                    endpoint: endpoint,
                    method: method,
                    status: response.status,
                    statusText: response.statusText,
                    headers: Object.fromEntries(response.headers.entries()),
                    data: data
                };
                
                if (response.ok) {
                    showResult('success', formatResult('✅ Success!', result));
                } else {
                    showResult('error', formatResult('❌ Error!', result));
                }
            } catch (error) {
                showResult('error', `❌ Network Error:\n\n${error.message}\n\nMake sure:\n1. XAMPP is running\n2. The proxy URL is correct\n3. PHP files are in the correct directory`);
            }
        }
        
        // Format result for display
        function formatResult(title, result) {
            return `${title}\n\n` +
                   `Endpoint: ${result.endpoint}\n` +
                   `Method: ${result.method}\n` +
                   `Status: ${result.status} ${result.statusText}\n\n` +
                   `Response:\n${JSON.stringify(result.data, null, 2)}`;
        }
        
        // Show result in results div
        function showResult(type, message) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.className = `result ${type}`;
            resultsDiv.innerHTML = `<pre>${message}</pre>`;
        }
        
        // Clear results
        function clearResults() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.className = 'result';
            resultsDiv.innerHTML = '<p style="color: #999;">Results cleared.</p>';
        }
        
        // Try to load saved cookies on page load
        window.addEventListener('DOMContentLoaded', function() {
            const stored = localStorage.getItem('chatgpt_cookies');
            if (stored) {
                const cookies = JSON.parse(stored);
                document.getElementById('sessionToken').value = cookies.sessionToken || '';
                document.getElementById('callbackUrl').value = cookies.callbackUrl || 'https%3A%2F%2Fchat.openai.com';
                document.getElementById('cfClearance').value = cookies.cfClearance || '';
            }
        });
    </script>
</body>
</html>
