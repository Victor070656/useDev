<?php

// Environment Configuration
define('APP_ENV', 'development'); // development | production
define('APP_NAME', 'DevAllies');
define('APP_URL', 'http://localhost/useDev2');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'devallies');

// Security
define('APP_KEY', 'dev_key_change_in_production_to_random_64_character_string_here');
define('SESSION_LIFETIME', 7200); // 2 hours in seconds

// Email Configuration
define('MAIL_FROM', 'noreply@devallies.com');
define('MAIL_FROM_NAME', 'DevAllies Platform');
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls'); // tls | ssl

// Payment Providers
define('STRIPE_ENABLED', false);
define('STRIPE_PUBLIC_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

define('PAYPAL_ENABLED', false);
define('PAYPAL_MODE', 'sandbox'); // sandbox | live
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_CLIENT_SECRET', '');

// Paystack Configuration
define('PAYSTACK_ENABLED', true);
define('PAYSTACK_PUBLIC_KEY', 'pk_test_...'); // Replace with your Paystack public key
define('PAYSTACK_SECRET_KEY', 'sk_test_...'); // Replace with your Paystack secret key
define('PAYSTACK_MODE', 'test'); // test | live

// AI Configuration (for matching engine)
define('AI_ENABLED', false);
define('AI_PROVIDER', 'openai'); // openai | anthropic | mock
define('AI_API_KEY', '');
define('AI_MODEL', 'gpt-4');

// File Upload Settings
define('MAX_UPLOAD_SIZE', 10485760); // 10MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);

// Platform Settings
define('PLATFORM_FEE_PERCENTAGE', 10); // 10% platform fee
define('MINIMUM_PAYOUT', 5000); // $50.00 in cents
define('CREATOR_TYPES', ['developer', 'designer']);

// Pagination
define('ITEMS_PER_PAGE', 20);
define('SEARCH_RESULTS_PER_PAGE', 24);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . '/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . '/error.log');
}
