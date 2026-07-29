<?php
// config/app.php

define('APP_ENV', 'production'); // Options: 'development' or 'production'

if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

define('SITE_NAME', 'UNI MOBILE REPAIRS');
define('SITE_URL', 'https://yourdomain.com');
?>