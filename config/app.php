<?php
/**
 * Application Configuration
 */

// App settings
define('APP_NAME', 'PIM System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development or production

// Base URL
define('BASE_URL', 'http://localhost:8000/');

// Timezone
date_default_timezone_set('UTC');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

?>
