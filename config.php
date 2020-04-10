<?php

// Define application environment
if (! defined('APPLICATION_ENV')) {
    $env = (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'dev');
    if (isset($_SERVER['argv']) && isset($_SERVER['argv'][2]) && $_SERVER['argv'][2]) {
        $env = $_SERVER['argv'][2];
    }
    define('APPLICATION_ENV', $env);
}

// Global config definition
set_time_limit(0);
ini_set('date.timezone', 'Europe/London');
ini_set('session.use_cookies', 1);

// Jwt secret signature key
define('BOSSANOVA_JWT_SECRET', 'MY-SECRET-KEY');
// Default Locale
define('DEFAULT_LOCALE', 'en_GB');
// Send a debug notification to an email below in case an query error
define('BOSSANOVA_DATABASE_DEBUG', '');
// Consider routes saved on the database
define('BOSSANOVA_DATABASE_ROUTES', 0);
// Consider login as valid routes
define('BOSSANOVA_LOGIN_ROUTES', 0);
// Request captcha code in case of consecutives login erros
define('BOSSANOVA_LOGIN_CAPTCHA', 1);
// Login via facebook
define('BOSSANOVA_LOGIN_VIA_FACEBOOK', 0);
// New user via facebook
define('BOSSANOVA_NEWUSER_VIA_FACEBOOK', 0);
// Autoload view
define('BOSSANOVA_AUTOLOAD_VIEW', 1);
// Base URL
define('BOSSANOVA_BASEURL', '');

// Global templates for erros
define('TEMPLATE_ERROR', 'default/error.html');

// Global mail server configuration
define('MS_CONFIG_TYPE', 'phpmailer');
define('MS_CONFIG_HOST', 'localhost');
define('MS_CONFIG_PORT', 25);
define('MS_CONFIG_FROM', '');
define('MS_CONFIG_NAME', '');
define('MS_CONFIG_USER', '');
define('MS_CONFIG_PASS', '');
define('MS_CONFIG_KEY', '');

// Login request email subject
define('EMAIL_RECOVERY_SUBJECT', 'Login Reset Request');
define('EMAIL_RECOVERY_FILE', 'resources/texts/recovery.txt');
define('EMAIL_REGISTRATION_FILE', 'resources/texts/registration.txt');
define('EMAIL_REGISTRATION_SUBJECT', 'Welcome!');

// Redis
define('REDIS_CONFIG_HOST', 'redis');
define('REDIS_CONFIG_PORT', '6379');

// Facebook
define('FACEBOOK_APPID', '');
define('FACEBOOK_SECRET', '');

// Different configurations depending on environment
if (APPLICATION_ENV == 'production') {
    // Disable all reporting
    ini_set('error_reporting', 0);
    ini_set('display_errors', 0);

    // Global database configuration
    define('DB_CONFIG_TYPE', 'pgsql');
    define('DB_CONFIG_HOST', 'localhost');
    define('DB_CONFIG_USER', '');
    define('DB_CONFIG_PASS', '');
    define('DB_CONFIG_NAME', '');
} else {
    // Enable all reporting in dev
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);

    // Global database configuration
    define('DB_CONFIG_TYPE', 'pgsql');
    define('DB_CONFIG_HOST', '');
    define('DB_CONFIG_USER', '');
    define('DB_CONFIG_PASS', '');
    define('DB_CONFIG_NAME', '');
}