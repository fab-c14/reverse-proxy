#!/bin/bash

# Example cURL commands for testing the ChatGPT Reverse Proxy
# Replace YOUR_TOKEN, YOUR_CALLBACK, and YOUR_CLEARANCE with actual cookie values

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Your proxy URL (change this to your actual proxy URL)
PROXY_URL="https://yourserver.com/chatgpt-proxy"

echo -e "${BLUE}=== ChatGPT Reverse Proxy - cURL Examples ===${NC}\n"

echo -e "${YELLOW}NOTE: Replace YOUR_TOKEN, YOUR_CALLBACK, and YOUR_CLEARANCE with actual values${NC}\n"

# Example cookies (REPLACE THESE)
SESSION_TOKEN="YOUR_TOKEN"
CALLBACK_URL="https%3A%2F%2Fchat.openai.com"  # URL-encoded: https://chat.openai.com
CF_CLEARANCE="YOUR_CLEARANCE"

echo -e "${GREEN}1. Check Session Status (HTTPS with verbose output)${NC}"
echo "This verifies HTTPS is working and shows the SSL handshake:"
echo ""
cat << 'EOF'
curl -k -v -X GET "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=https%3A%2F%2Fchat.openai.com; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json"
EOF
echo ""
echo -e "${BLUE}Expected: JSON with session info, showing SSL/TLS in verbose output${NC}"
echo ""
echo "---"
echo ""

echo -e "${GREEN}2. Alternative: Using X-ChatGPT-Cookies Header${NC}"
echo "This method passes cookies via a custom header instead of Cookie header:"
echo ""
cat << 'EOF'
curl -k -v -X GET "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "X-ChatGPT-Cookies: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json"
EOF
echo ""
echo -e "${BLUE}Expected: Same JSON response as method 1${NC}"
echo ""
echo "---"
echo ""

echo -e "${GREEN}3. Verify HTTPS/TLS Connection${NC}"
echo "Filter output to show only SSL/TLS information:"
echo ""
cat << 'EOF'
curl -v "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  2>&1 | grep -E "(SSL|TLS|HTTP/)"
EOF
echo ""
echo -e "${BLUE}Expected output:${NC}"
echo "  * SSL connection using TLSv1.3 / TLS_AES_256_GCM_SHA384"
echo "  > GET /chatgpt-proxy/api/auth/session HTTP/2"
echo "  < HTTP/2 200"
echo ""
echo "---"
echo ""

echo -e "${GREEN}4. Access ChatGPT Models List${NC}"
echo ""
cat << 'EOF'
curl -k -X GET "https://yourserver.com/chatgpt-proxy/backend-api/models" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json" \
  -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
EOF
echo ""
echo -e "${BLUE}Expected: JSON array of available models${NC}"
echo ""
echo "---"
echo ""

echo -e "${GREEN}5. Access ChatGPT Conversations${NC}"
echo ""
cat << 'EOF'
curl -k -X GET "https://yourserver.com/chatgpt-proxy/backend-api/conversations?offset=0&limit=20" \
  -H "Cookie: __Secure-next-auth.session-token=YOUR_TOKEN; __Secure-next-auth.callback-url=YOUR_CALLBACK; cf_clearance=YOUR_CLEARANCE" \
  -H "Accept: application/json"
EOF
echo ""
echo -e "${BLUE}Expected: JSON with list of conversations${NC}"
echo ""
echo "---"
echo ""

echo -e "${GREEN}6. Test with Missing Cookies (Error Handling)${NC}"
echo "This should fail and return an error message:"
echo ""
cat << 'EOF'
curl -k -X GET "https://yourserver.com/chatgpt-proxy/api/auth/session" \
  -H "Accept: application/json"
EOF
echo ""
echo -e "${BLUE}Expected: JSON error with message about missing cookies${NC}"
echo ""
echo "---"
echo ""

echo -e "${YELLOW}To run actual tests, replace placeholders and execute the commands above.${NC}"
echo ""
echo -e "${GREEN}How to get your cookies:${NC}"
echo "1. Log in to https://chat.openai.com in your browser"
echo "2. Press F12 to open Developer Tools"
echo "3. Go to Application/Storage tab → Cookies → https://chat.openai.com"
echo "4. Copy the values of:"
echo "   - __Secure-next-auth.session-token"
echo "   - __Secure-next-auth.callback-url"
echo "   - cf_clearance"
