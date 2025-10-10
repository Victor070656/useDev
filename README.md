# DevAllies - Plain PHP Version

This is a plain PHP (non-MVC) version of the DevAllies platform, converted from the MVC architecture.

## Project Structure

```
useDev2/
├── includes/           # Core includes
│   ├── config.php     # Configuration settings
│   ├── db.php         # Database connection functions
│   ├── functions.php  # Helper functions
│   ├── init.php       # Initialization file
│   ├── header.php     # Global header template
│   └── footer.php     # Global footer template
├── assets/            # Static assets
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── images/       # Images
├── uploads/          # User uploaded files
│   ├── profiles/
│   ├── portfolios/
│   ├── products/
│   └── courses/
├── database/         # Database files
├── migrations/       # Database migrations
├── logs/            # Log files
├── admin/           # Admin pages
├── creator/         # Creator pages
├── client/          # Client pages
├── index.php        # Homepage
├── login.php        # Login page
├── register.php     # Registration page
├── logout.php       # Logout handler
├── forgot-password.php
├── reset-password.php
├── verify-email.php
├── .htaccess        # Apache configuration
└── README.md        # This file
```

## Key Changes from MVC Version

1. **No Router**: Direct PHP files instead of routing system
2. **No Controllers**: Business logic embedded directly in page files
3. **No Models**: Database functions in includes/functions.php
4. **Simpler Structure**: Each page is self-contained
5. **Direct Includes**: Uses require_once instead of autoloading

## Installation

1. **Configure Database**:
   ```bash
   # Edit includes/config.php with your database credentials
   ```

2. **Run Migrations**:
   ```bash
   cd /opt/lampp/htdocs/useDev2
   php database/run_migrations.php
   ```

3. **Set Permissions**:
   ```bash
   chmod -R 755 /opt/lampp/htdocs/useDev2
   chmod -R 777 /opt/lampp/htdocs/useDev2/uploads
   chmod -R 777 /opt/lampp/htdocs/useDev2/logs
   ```

4. **Access the Application**:
   - URL: http://localhost/useDev2
   - Make sure Apache is running

## Configuration

Edit `includes/config.php` to configure:

- Database connection
- App URL and environment
- Email settings (SMTP)
- Payment providers (Stripe, PayPal)
- AI integration settings
- File upload limits
- Security settings

## Database Setup

The application uses MySQL/MariaDB. Default connection:
- Host: localhost
- User: root
- Password: (empty)
- Database: devallies

Run migrations to create all required tables:
```bash
php database/run_migrations.php
```

## Security Features

- CSRF protection on all forms
- XSS prevention with output escaping
- SQL injection prevention with prepared statements
- Password hashing with bcrypt
- Secure session handling
- File upload validation
- Input sanitization

## Helper Functions

All helper functions are in `includes/functions.php`:

### Authentication
- `is_authenticated()` - Check if user is logged in
- `require_auth()` - Require authentication
- `require_role($role)` - Require specific user role
- `get_user_id()` - Get current user ID
- `get_user_type()` - Get current user type

### Security
- `csrf_token()` - Generate CSRF token
- `csrf_field()` - Generate CSRF hidden field
- `verify_csrf_token($token)` - Verify CSRF token
- `sanitize_input($data)` - Sanitize user input
- `escape_output($data)` - Escape output for HTML

### Database
- `get_db_connection()` - Get database connection
- `db_prepare($sql)` - Prepare SQL statement
- `db_query($sql)` - Execute query
- `db_escape($value)` - Escape value
- `find_user_by_email($email)` - Find user by email
- `create_user($data)` - Create new user
- `verify_user_email($token)` - Verify email

### Navigation
- `redirect($path)` - Redirect to path
- `url($path)` - Generate URL
- `asset($path)` - Get asset URL

### Flash Messages
- `set_flash($key, $message)` - Set flash message
- `get_flash($key)` - Get and clear flash message
- `has_flash($key)` - Check if flash exists

### Validation
- `validate_email($email)` - Validate email
- `validate_required($value)` - Check if required
- `validate_min_length($value, $min)` - Validate min length

### Utilities
- `format_date($date)` - Format date
- `format_money($cents)` - Format money
- `slugify($text)` - Create URL slug
- `truncate($text, $length)` - Truncate text

## Page Structure

Each page follows this pattern:

```php
<?php
require_once 'includes/init.php';

// Authentication check (if needed)
require_auth();

// Handle POST requests
if (is_post()) {
    // Process form data
    // Redirect or show errors
}

// Page variables
$pageTitle = 'Page Title - ' . APP_NAME;

// Include header
require_once 'includes/header.php';
?>

<!-- Page HTML content here -->

<?php
// Include footer
require_once 'includes/footer.php';
?>
```

## User Types

The platform supports three user types:

1. **Admin**: Full platform access
2. **Creator**: Developers/Designers who offer services
3. **Client**: Businesses looking to hire creators

## Quick Start

1. Visit http://localhost/useDev2
2. Click "Get Started Free"
3. Choose Creator or Client
4. Fill in registration form
5. Login with credentials

## Testing

For quick testing, use these credentials on login page:
- Developer: john.dev@example.com / password
- Client: client@startup.com / password

## Differences from Original MVC Version

| Feature | MVC Version | Plain PHP Version |
|---------|------------|-------------------|
| Routing | Custom Router class | Direct file access |
| Controllers | Separate controller classes | Logic in page files |
| Models | Model classes | Functions in includes/functions.php |
| Views | Separate view files | Embedded in page files |
| Autoloading | Required for classes | Simple require_once |
| URL Format | /controller/method | /page.php |
| Structure | app/ directory | Root level files |

## Development

- Environment: Set APP_ENV in config.php
- Error Logging: Check logs/ directory
- Database Logs: Check logs/database.log
- Error Logs: Check logs/error.log

## Support

For issues or questions about this plain PHP version, refer to the original MVC documentation or check the TODO.md file for planned features.

## License

Same as original DevAllies project.
