# wordpress-n8n-chatbot
Small application connecting N8N AI Agent to a Chatbot for Wordpress.

User Manual:
================

1. Installation:
- Download the plugin ZIP file
- Go to WordPress Admin → Plugins → Add New → Upload Plugin
- Upload and activate the plugin

2. Requirements:
- PHP 7.4 or higher
- cURL extension enabled
- WordPress REST API enabled
- HTTPS recommended for security

3. Configuration:
- No configuration needed - works out of the box
- Ensure CORS is properly configured if using external domains
- Test the chat interface by clicking the chatbot icon

4. Customization:
- Replace images in /public/ directory:
  - chatbot-icon.png (recommended size: 60x60px)
  - bot-thumb.png (recommended size: 40x40px)
- Modify chatbot-style.css for visual changes
- Adjust chat window size in CSS:
  .oacb-chat-window { width: 400px; }

5. Security Considerations:
- All API requests are protected with nonces
- Input sanitization and output escaping implemented
- Session IDs generated with cryptographically secure random_bytes
- CSRF protection through WordPress REST API nonces

6. Troubleshooting:
- Check browser console for JavaScript errors
- Verify REST API is working: /wp-json/oacb/v1/send-message
- Ensure webhook URL is correct and accessible
- Check server error logs for PHP issues

This implementation follows security best practices:
1. Input validation and sanitization
2. Output escaping
3. CSRF protection with nonces
4. Secure session ID generation
5. HTTPS enforcement for API calls
6. Proper error handling without exposing details
7. Rate limiting through WordPress REST API
8. Content Security Policy (CSP) friendly

Performance optimizations:
1. Minimal database usage
2. Efficient DOM manipulation
3. Caching of static assets
4. Asynchronous API calls
5. Lightweight CSS/JS payloads
6. Proper asset versioning
7. Lazy loading of chat interface