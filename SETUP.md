# DevAllies Plain PHP - Quick Setup Guide

## Prerequisites

- ✅ XAMPP/LAMPP installed
- ✅ Apache running
- ✅ MySQL/MariaDB running
- ✅ PHP 7.4 or higher

## Installation Steps

### 1. Verify Installation Location

Your project should be at:
```
/opt/lampp/htdocs/useDev2/
```

### 2. Configure Database

Edit the database configuration:
```bash
nano /opt/lampp/htdocs/useDev2/includes/config.php
```

Update these lines if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
define('DB_NAME', 'devallies');
```

### 3. Create Database

Open MySQL:
```bash
/opt/lampp/bin/mysql -u root -p
```

Create the database:
```sql
CREATE DATABASE IF NOT EXISTS devallies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 4. Run Migrations

Execute the migration script:
```bash
cd /opt/lampp/htdocs/useDev2
php database/run_migrations.php
```

You should see:
```
✓ Migration 001_create_users_table.sql executed successfully
✓ Migration 002_create_creator_profiles.sql executed successfully
✓ Migration 003_create_project_briefs.sql executed successfully
... and so on
```

### 5. Set Permissions

Set proper file permissions:
```bash
# Make all files readable
chmod -R 755 /opt/lampp/htdocs/useDev2

# Make uploads writable
chmod -R 777 /opt/lampp/htdocs/useDev2/uploads

# Make logs writable
chmod -R 777 /opt/lampp/htdocs/useDev2/logs
```

### 6. Verify Apache Configuration

Check if Apache is running:
```bash
/opt/lampp/lampp status
```

If not running, start it:
```bash
sudo /opt/lampp/lampp start
```

### 7. Test the Application

Open your browser and visit:
```
http://localhost/useDev2
```

You should see the DevAllies homepage with purple gradient design.

## Testing the Application

### Test User Registration

1. Go to: `http://localhost/useDev2/register.php`
2. Fill in the form:
   - First Name: John
   - Last Name: Doe
   - Email: test@example.com
   - Password: password123
   - Account Type: Creator
   - Creator Type: Developer (if creator)
3. Click "Create Account"
4. You should be redirected to login

### Test Login

1. Go to: `http://localhost/useDev2/login.php`
2. Use quick login buttons for testing:
   - **Developer**: john.dev@example.com / password
   - **Client**: client@startup.com / password
3. You should be redirected to the appropriate dashboard

### Test Dashboards

**Creator Dashboard**:
```
http://localhost/useDev2/creator/dashboard.php
```
Shows: Proposals, earnings, portfolio, quick actions

**Client Dashboard**:
```
http://localhost/useDev2/client/dashboard.php
```
Shows: Active briefs, proposals, hired creators

**Admin Dashboard**:
```
http://localhost/useDev2/admin/dashboard.php
```
Shows: Platform statistics, recent activity, user management

### Test Browse Page

```
http://localhost/useDev2/browse.php
```
Shows: Creator profiles, filtering, search

## Common Issues & Solutions

### Issue 1: Database Connection Failed

**Error**: "Database connection failed. Please try again later."

**Solution**:
```bash
# Check if MySQL is running
/opt/lampp/lampp status

# Start MySQL if not running
sudo /opt/lampp/lampp startmysql

# Verify credentials in includes/config.php
```

### Issue 2: 404 Not Found

**Error**: All pages show 404

**Solution**:
```bash
# Check .htaccess exists
ls -la /opt/lampp/htdocs/useDev2/.htaccess

# Enable mod_rewrite in Apache
sudo /opt/lampp/bin/apachectl -M | grep rewrite

# If not enabled, edit httpd.conf
sudo nano /opt/lampp/etc/httpd.conf
# Find and uncomment: LoadModule rewrite_module modules/mod_rewrite.so
```

### Issue 3: File Upload Fails

**Error**: "Failed to move uploaded file"

**Solution**:
```bash
# Set correct permissions
chmod -R 777 /opt/lampp/htdocs/useDev2/uploads

# Check PHP upload limits
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Edit php.ini if needed
sudo nano /opt/lampp/etc/php.ini
# Set: upload_max_filesize = 10M
# Set: post_max_size = 10M
```

### Issue 4: Session Not Working

**Error**: Login doesn't persist

**Solution**:
```bash
# Check session save path permissions
php -i | grep session.save_path

# Make session directory writable
sudo chmod 777 /opt/lampp/temp
```

### Issue 5: Flash Messages Not Showing

**Error**: Success/error messages don't appear

**Solution**:
- Clear browser cache
- Check browser console for JavaScript errors
- Verify Alpine.js is loading:
  ```html
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  ```

## Verification Checklist

Run through this checklist to verify everything works:

- [ ] Homepage loads (http://localhost/useDev2)
- [ ] Can register new user
- [ ] Can login with test credentials
- [ ] Creator dashboard loads and shows stats
- [ ] Client dashboard loads and shows stats
- [ ] Admin dashboard loads (login as admin)
- [ ] Browse page shows creators
- [ ] Flash messages appear on actions
- [ ] Logout works correctly
- [ ] Password reset flow works
- [ ] Email verification works
- [ ] 404 page shows for invalid URLs
- [ ] File uploads work (profile pictures)
- [ ] Database queries execute without errors

## Default Test Users

After running migrations, these test users are available:

### Developer Account
- Email: `john.dev@example.com`
- Password: `password`
- Type: Creator (Developer)

### Designer Account
- Email: `sarah.design@example.com`
- Password: `password`
- Type: Creator (Designer)

### Client Account
- Email: `client@startup.com`
- Password: `password`
- Type: Client

### Admin Account
- Email: `admin@devallies.com`
- Password: `admin123`
- Type: Admin

## Configuration Options

### Change App URL

Edit `includes/config.php`:
```php
define('APP_URL', 'http://localhost/useDev2');
// Change to your domain in production
```

### Enable Production Mode

Edit `includes/config.php`:
```php
define('APP_ENV', 'production');
```

This will:
- Disable error display
- Enable secure cookies
- Require email verification
- Hide debug information

### Configure Email

Edit `includes/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

### Configure Payment Providers

Edit `includes/config.php`:
```php
// Stripe
define('STRIPE_PUBLIC_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');

// PayPal
define('PAYPAL_CLIENT_ID', 'your-client-id');
define('PAYPAL_CLIENT_SECRET', 'your-client-secret');
```

## Next Steps

After successful setup:

1. **Customize Design**: Edit includes/header.php and includes/footer.php
2. **Add Features**: Create new pages following the pattern in existing files
3. **Configure Email**: Set up SMTP for password reset emails
4. **Add Payment**: Integrate Stripe or PayPal for transactions
5. **Deploy**: Move to production server with proper security

## Getting Help

- Check README.md for full documentation
- Review CONVERSION_SUMMARY.md for architecture details
- Look at existing pages for code examples
- Check logs/error.log for error details
- Review includes/functions.php for available helpers

## Security Notes

⚠️ **Before going to production**:

1. Change APP_KEY in config.php to a random 64-character string
2. Set APP_ENV to 'production'
3. Disable test user quick login buttons
4. Set strong MySQL root password
5. Enable HTTPS with SSL certificate
6. Configure proper email verification
7. Set up regular database backups
8. Enable security headers in .htaccess
9. Review and update CORS settings
10. Implement rate limiting

## Support

For issues or questions:
- Review error logs in `/opt/lampp/htdocs/useDev2/logs/`
- Check Apache error log: `/opt/lampp/logs/error_log`
- Check PHP error log: configured in includes/config.php

---

**Setup Complete!** 🎉

Your DevAllies plain PHP application is now ready to use.

Access it at: **http://localhost/useDev2**
