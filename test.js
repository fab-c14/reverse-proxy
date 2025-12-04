#!/usr/bin/env node

/**
 * ChatGPT Reverse Proxy - Test Suite
 * 
 * This script provides an easy way to test the proxy with all major endpoints.
 * Run with: node test.js
 * 
 * Requirements: Node.js 18+ (for native fetch API)
 */

// ============================================================================
// CONFIGURATION - UPDATE THESE VALUES WITH YOUR ACTUAL COOKIES
// ============================================================================

const CONFIG = {
  // Your proxy URL (e.g., https://yourserver.com/chatgpt-proxy or http://localhost/chatgpt-proxy)
  PROXY_URL: 'https://yourserver.com/chatgpt-proxy',
  
  // Your ChatGPT cookies - Get these from your browser after logging into chat.openai.com
  // How to get cookies:
  // 1. Log in to https://chat.openai.com
  // 2. Press F12 to open Developer Tools
  // 3. Go to Application/Storage tab → Cookies → https://chat.openai.com
  // 4. Copy the values below
  COOKIES: {
    '__Secure-next-auth.session-token': 'YOUR_SESSION_TOKEN_HERE',
    '__Secure-next-auth.callback-url': 'https%3A%2F%2Fchat.openai.com', // URL-encoded: https://chat.openai.com
    'cf_clearance': 'YOUR_CF_CLEARANCE_HERE'
  },
  
  // Test configuration
  VERBOSE: true, // Set to false for less output
  TIMEOUT: 30000, // Request timeout in milliseconds
};

// ============================================================================
// TEST SUITE
// ============================================================================

const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  green: '\x1b[32m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  cyan: '\x1b[36m',
};

class ProxyTester {
  constructor(config) {
    this.config = config;
    this.results = {
      passed: 0,
      failed: 0,
      skipped: 0,
    };
  }

  /**
   * Build cookie header string from config
   */
  buildCookieHeader() {
    return Object.entries(this.config.COOKIES)
      .map(([name, value]) => `${name}=${value}`)
      .join('; ');
  }

  /**
   * Make HTTP request to proxy
   */
  async makeRequest(endpoint, options = {}) {
    const url = `${this.config.PROXY_URL}${endpoint}`;
    const cookieHeader = this.buildCookieHeader();
    
    const defaultOptions = {
      method: 'GET',
      headers: {
        'Cookie': cookieHeader,
        'Accept': 'application/json',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
      },
    };

    const mergedOptions = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...options.headers,
      },
    };

    if (this.config.VERBOSE) {
      console.log(`${colors.cyan}→ ${mergedOptions.method} ${url}${colors.reset}`);
    }

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), this.config.TIMEOUT);

      const response = await fetch(url, {
        ...mergedOptions,
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      const contentType = response.headers.get('content-type');
      let data;

      if (contentType && contentType.includes('application/json')) {
        data = await response.json();
      } else {
        data = await response.text();
      }

      return {
        ok: response.ok,
        status: response.status,
        statusText: response.statusText,
        headers: Object.fromEntries(response.headers.entries()),
        data,
      };
    } catch (error) {
      if (error.name === 'AbortError') {
        throw new Error(`Request timeout after ${this.config.TIMEOUT}ms`);
      }
      throw error;
    }
  }

  /**
   * Print test header
   */
  printHeader(title) {
    console.log('\n' + '='.repeat(70));
    console.log(`${colors.bright}${colors.blue}${title}${colors.reset}`);
    console.log('='.repeat(70));
  }

  /**
   * Print test result
   */
  printResult(testName, passed, message = '', data = null) {
    const icon = passed ? '✓' : '✗';
    const color = passed ? colors.green : colors.red;
    
    console.log(`${color}${icon} ${testName}${colors.reset}`);
    
    if (message) {
      console.log(`  ${message}`);
    }
    
    if (data && this.config.VERBOSE) {
      const dataStr = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
      const preview = dataStr.length > 500 ? dataStr.substring(0, 500) + '...' : dataStr;
      console.log(`  ${colors.cyan}Response:${colors.reset}`, preview);
    }

    if (passed) {
      this.results.passed++;
    } else {
      this.results.failed++;
    }
  }

  /**
   * Run a single test
   */
  async runTest(testName, testFn) {
    try {
      await testFn();
    } catch (error) {
      this.printResult(testName, false, `Error: ${error.message}`);
    }
  }

  /**
   * Validate configuration
   */
  validateConfig() {
    this.printHeader('Configuration Validation');

    const hasValidUrl = this.config.PROXY_URL && !this.config.PROXY_URL.includes('yourserver.com');
    const hasValidToken = this.config.COOKIES['__Secure-next-auth.session-token'] && 
                         !this.config.COOKIES['__Secure-next-auth.session-token'].includes('YOUR_');
    const hasValidClearance = this.config.COOKIES['cf_clearance'] && 
                             !this.config.COOKIES['cf_clearance'].includes('YOUR_');

    if (!hasValidUrl) {
      console.log(`${colors.yellow}⚠ Warning: PROXY_URL is not configured (still has placeholder value)${colors.reset}`);
      console.log(`  Please update PROXY_URL in the CONFIG section at the top of this file`);
    }

    if (!hasValidToken || !hasValidClearance) {
      console.log(`${colors.yellow}⚠ Warning: Cookies are not configured (still have placeholder values)${colors.reset}`);
      console.log(`  Please update the COOKIES in the CONFIG section with your actual cookies`);
      console.log(`  See instructions at the top of this file for how to get them`);
      return false;
    }

    console.log(`${colors.green}✓ Configuration looks valid${colors.reset}`);
    console.log(`  Proxy URL: ${this.config.PROXY_URL}`);
    console.log(`  Session Token: ${this.config.COOKIES['__Secure-next-auth.session-token'].substring(0, 20)}...`);
    return true;
  }

  /**
   * Test 1: Check session status
   */
  async testSessionStatus() {
    await this.runTest('Test 1: Session Status', async () => {
      const response = await this.makeRequest('/api/auth/session');

      if (!response.ok) {
        this.printResult('Session Status', false, `HTTP ${response.status}: ${response.statusText}`);
        return;
      }

      const hasUser = response.data && response.data.user;
      const hasAccessToken = response.data && response.data.accessToken;

      if (hasUser || hasAccessToken) {
        this.printResult('Session Status', true, 'Session is valid and active', response.data);
      } else {
        this.printResult('Session Status', false, 'Unexpected response structure', response.data);
      }
    });
  }

  /**
   * Test 2: Get available models
   */
  async testGetModels() {
    await this.runTest('Test 2: Get Models', async () => {
      const response = await this.makeRequest('/backend-api/models');

      if (!response.ok) {
        this.printResult('Get Models', false, `HTTP ${response.status}: ${response.statusText}`);
        return;
      }

      const hasModels = response.data && (response.data.models || Array.isArray(response.data));
      
      if (hasModels) {
        const modelCount = response.data.models ? response.data.models.length : response.data.length;
        this.printResult('Get Models', true, `Retrieved ${modelCount} models`, response.data);
      } else {
        this.printResult('Get Models', false, 'Unexpected response structure', response.data);
      }
    });
  }

  /**
   * Test 3: Get conversations
   */
  async testGetConversations() {
    await this.runTest('Test 3: Get Conversations', async () => {
      const response = await this.makeRequest('/backend-api/conversations?offset=0&limit=20');

      if (!response.ok) {
        this.printResult('Get Conversations', false, `HTTP ${response.status}: ${response.statusText}`);
        return;
      }

      const hasItems = response.data && (response.data.items !== undefined);
      
      if (hasItems) {
        const count = response.data.items ? response.data.items.length : 0;
        this.printResult('Get Conversations', true, `Retrieved ${count} conversations`, response.data);
      } else {
        this.printResult('Get Conversations', false, 'Unexpected response structure', response.data);
      }
    });
  }

  /**
   * Test 4: Test error handling (missing cookies)
   */
  async testErrorHandling() {
    await this.runTest('Test 4: Error Handling', async () => {
      // Make request without cookies
      const url = `${this.config.PROXY_URL}/api/auth/session`;
      
      if (this.config.VERBOSE) {
        console.log(`${colors.cyan}→ GET ${url} (without cookies)${colors.reset}`);
      }

      try {
        const response = await fetch(url, {
          headers: {
            'Accept': 'application/json',
          },
        });

        const data = await response.json();

        if (!response.ok && data.error) {
          this.printResult('Error Handling', true, 'Proxy correctly returns error for missing cookies', data);
        } else {
          this.printResult('Error Handling', false, 'Expected error response but got success', data);
        }
      } catch (error) {
        this.printResult('Error Handling', false, `Unexpected error: ${error.message}`);
      }
    });
  }

  /**
   * Test 5: Test with custom header method
   */
  async testCustomHeader() {
    await this.runTest('Test 5: Custom Header Method', async () => {
      const cookieString = this.buildCookieHeader();
      const url = `${this.config.PROXY_URL}/api/auth/session`;
      
      if (this.config.VERBOSE) {
        console.log(`${colors.cyan}→ GET ${url} (using X-ChatGPT-Cookies header)${colors.reset}`);
      }

      try {
        const response = await fetch(url, {
          headers: {
            'X-ChatGPT-Cookies': cookieString,
            'Accept': 'application/json',
          },
        });

        const data = await response.json();

        if (response.ok && (data.user || data.accessToken)) {
          this.printResult('Custom Header Method', true, 'X-ChatGPT-Cookies header method works', data);
        } else {
          this.printResult('Custom Header Method', false, 'Custom header method failed', data);
        }
      } catch (error) {
        this.printResult('Custom Header Method', false, `Error: ${error.message}`);
      }
    });
  }

  /**
   * Test 6: Check HTTPS enforcement
   */
  async testHttpsEnforcement() {
    await this.runTest('Test 6: HTTPS Check', async () => {
      const isHttps = this.config.PROXY_URL.startsWith('https://');
      
      if (isHttps) {
        this.printResult('HTTPS Check', true, 'Proxy URL uses HTTPS (recommended)');
      } else {
        console.log(`${colors.yellow}⚠ Warning: Proxy URL uses HTTP instead of HTTPS${colors.reset}`);
        console.log(`  For production use, HTTPS is strongly recommended to protect cookies`);
        this.results.skipped++;
      }
    });
  }

  /**
   * Print final summary
   */
  printSummary() {
    this.printHeader('Test Summary');

    const total = this.results.passed + this.results.failed + this.results.skipped;
    const passRate = total > 0 ? ((this.results.passed / total) * 100).toFixed(1) : 0;

    console.log(`Total Tests: ${total}`);
    console.log(`${colors.green}Passed: ${this.results.passed}${colors.reset}`);
    console.log(`${colors.red}Failed: ${this.results.failed}${colors.reset}`);
    console.log(`${colors.yellow}Skipped: ${this.results.skipped}${colors.reset}`);
    console.log(`Pass Rate: ${passRate}%`);

    if (this.results.failed === 0) {
      console.log(`\n${colors.green}${colors.bright}🎉 All tests passed!${colors.reset}`);
    } else {
      console.log(`\n${colors.red}${colors.bright}❌ Some tests failed. Please check the output above.${colors.reset}`);
    }

    console.log('\n' + '='.repeat(70) + '\n');
  }

  /**
   * Run all tests
   */
  async runAllTests() {
    console.log(`${colors.bright}${colors.blue}ChatGPT Reverse Proxy - Test Suite${colors.reset}`);
    console.log('Starting tests...\n');

    // Validate configuration first
    const configValid = this.validateConfig();
    
    if (!configValid) {
      console.log(`\n${colors.red}Cannot run tests without valid configuration.${colors.reset}`);
      console.log('Please update the CONFIG section at the top of this file.\n');
      return;
    }

    // Run all tests
    this.printHeader('Running Tests');
    
    await this.testSessionStatus();
    await this.testGetModels();
    await this.testGetConversations();
    await this.testErrorHandling();
    await this.testCustomHeader();
    await this.testHttpsEnforcement();

    // Print summary
    this.printSummary();
  }
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

async function main() {
  const tester = new ProxyTester(CONFIG);
  
  try {
    await tester.runAllTests();
  } catch (error) {
    console.error(`${colors.red}Fatal error: ${error.message}${colors.reset}`);
    console.error(error.stack);
    process.exit(1);
  }
}

// Check Node.js version
const nodeVersion = parseInt(process.version.slice(1).split('.')[0]);
if (nodeVersion < 18) {
  console.error(`${colors.red}Error: This script requires Node.js 18 or higher (for native fetch API)${colors.reset}`);
  console.error(`Current version: ${process.version}`);
  console.error('Please upgrade Node.js or use a version with fetch support.');
  process.exit(1);
}

// Run the tests
main();
