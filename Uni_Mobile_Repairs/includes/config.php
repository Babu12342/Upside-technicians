<?php
// includes/config.php
// Centralized configuration for sensitive credentials and system settings

define('TELEGRAM_BOT_TOKEN', '8874653683:AAE4KpvDuTvHGabZTe5oIo9lW5oKpviqbIs');
define('TELEGRAM_CHAT_ID', '5232258264');

// Set error logging (Hide errors from public output, log internally)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);