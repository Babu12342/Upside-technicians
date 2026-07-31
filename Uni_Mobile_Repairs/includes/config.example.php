<?php
// includes/config.example.php
// Copy this file to includes/config.php on the server and fill in real values.

return [
    'db' => [
        'host' => 'sql209.infinityfree.com',
        'port' => '3306',
        'name' => 'if0_42537540_XXX', // replace XXX with the exact database name
        'user' => 'if0_42537540',
        'pass' => 'mQ2em3dfGg'
    ],

    // MPESA or other API credentials should be set here or via environment variables in production
    'mpesa' => [
        'oauth_url' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'consumer_key' => '',
        'consumer_secret' => ''
    ],

    'error_log' => __DIR__ . '/../error.log'
];
