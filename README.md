# Live Code Editor with AI Help

## 🚀 Features

- **Real-time Code Editing**: Multiple users can collaborate on the same code
- **AI-Powered Assistance**: Get smart suggestions, bug fixes, and code explanations
- **Multiple Languages**: JavaScript, Python, PHP, HTML, CSS support
- **Auto-save**: Your code is automatically saved every 2 seconds
- **Live Code Execution**: Run JavaScript code directly in browser
- **Modern UI**: Beautiful, responsive interface with dark theme editor
- **Real-time Collaboration**: See live users and share sessions

## 🛠️ Installation & Setup

### Prerequisites
- Web server (Apache/Nginx/XAMPP/WAMP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- OpenAI API key (optional but recommended)

### Step 1: Download & Extract
```bash
# Clone or download the project files
# Extract to your web server directory (htdocs/www/public_html)
```

### Step 2: Database Setup
1. Create a new MySQL database named `live_code_editor`
2. Import the SQL file:
```sql
-- Run the contents of setup.sql in your MySQL database
-- Or use phpMyAdmin to import the file
```

### Step 3: Configure Database Connection
Edit `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_username');    // Change this
define('DB_PASS', 'your_mysql_password');    // Change this
define('DB_NAME', 'live_code_editor');
```

### Step 4: Get OpenAI API Key (Optional)
1. Go to [OpenAI Platform](https://platform.openai.com/api-keys)
2. Create an account and generate an API key
3. Add it to `php/config.php`:
```php
define('OPENAI_API_KEY', 'sk-your-actual-api-key-here');
```

### Step 5: File Permissions
Set proper permissions (Linux/Mac):
```bash
chmod 755 php/
chmod 644 php/*.php
chmod 666 errors.log  # Create this file for error logging
```

### Step 6: Test Installation
1. Open your browser
2. Navigate to `http://localhost/your-project-folder/`
3. You should see the Live Code Editor interface
4. Try typing some code and saving it

## 🎯 Usage Guide

### Basic Features
- **Write Code**: Use the main editor with syntax highlighting
- **Save**: Click Save button or use Ctrl+S
- **Run Code**: Click Run button or use Ctrl+Enter (JavaScript only)
- **Change Language**: Use the language dropdown

### AI Features
- **Get Suggestions**: Click "Get Code Suggestions" for improvement tips
- **Fix Code**: Click "Fix My Code" to identify and fix errors
- **Explain Code**: Click "Explain Code" for detailed explanations

### Collaboration
- Share your session ID with others
- Multiple users can edit the same code simultaneously
- Changes are synced in real-time

## 🔧 Configuration Options

### Enabling More Languages
To add support for more programming languages:

1. Add the language option in HTML:
```html
<option value="newlang">New Language</option>
```

2. Add CodeMirror mode:
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/newlang/newlang.min.js"></script>
```

3. Update the mode mapping in JavaScript:
```javascript
const modeMap = {
    // ... existing languages
    'newlang': 'newlang'
};
```

### Customizing AI Prompts
Edit the prompts in `php/ai_helper.php`:
```php
$prompts = [
    'suggestion' => "Your custom prompt here...",
    'fix' => "Your custom fix prompt...",
    'explain' => "Your custom explanation prompt..."
];
```

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check your database credentials in `php/config.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **AI Not Working**
   - Check if OpenAI API key is set correctly
   - Verify you have API credits
   - Check error logs in `errors.log`

3. **Code Not Saving**
   - Check file permissions
   - Verify database connection
   - Look for JavaScript console errors

4. **Real-time Features Not Working**
   - Ensure your web server supports PHP
   - Check browser console for errors
   - Verify AJAX requests are working

### Debug Mode
To enable detailed error reporting, add this to the top of PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🔒 Security Considerations

### Production Deployment
1. **Remove Debug Code**: Disable error display in production
2. **Secure API Keys**: Store API keys in environment variables
3. **Input Validation**: All user inputs are sanitized
4. **SQL Injection Prevention**: Using prepared statements
5. **Rate Limiting**: Consider adding rate limits for AI requests

### Database Security
```php
// Example of secure database connection
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);
```

## 🎨 Customization

### Changing Themes
The editor uses CodeMirror themes. To change:
1. Add new theme CSS file
2. Update the theme option:
```javascript
editor.setOption('theme', 'your-theme-name');
```

### UI Customization
- Edit CSS styles in the `<style>` section of `index.html`
- Use Bootstrap classes for responsive design
- Customize colors, fonts, and layouts

## 📝 API Endpoints

### Save Code
```
POST /php/save_code.php
Parameters: session, code, language
```

### Get Code
```
GET /php/get_code.php?session=SESSION_ID
```

### AI Helper
```
POST /php/ai_helper.php
Parameters: code, language, type, session
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📜 License

This project is open source and available under the MIT License.

## 🆘 Support

If you encounter any issues:
1. Check the troubleshooting section above
2. Look at browser console and `errors.log` file
3. Ensure all prerequisites are met
4. Verify your configuration settings

## 🚀 Future Enhancements

- WebSocket integration for true real-time collaboration
- More programming languages support
- Code version history
- Team management features
- Advanced AI code analysis
- Mobile app version

---

**Happy Coding! 🎉**

Made with ❤️ for developers who love to code collaboratively with AI assistance.