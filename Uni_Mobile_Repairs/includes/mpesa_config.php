<?php
// includes/mpesa_config.php

// Set to 'sandbox' for testing, or 'live' when your Till/Paybill is ready
define('MPESA_ENV', 'sandbox'); 

if (MPESA_ENV === 'sandbox') {
    // Safaricom Sandbox Test Credentials
    define('MPESA_CONSUMER_KEY', 'x5A9A7v7Y4b4xG1B8kK9L0mN3pO5qR7s'); // Standard Sandbox Key
    define('MPESA_CONSUMER_SECRET', 'aB1cD2eF3gH4iJ5kL6mN7oP8qR9s0t1u'); // Standard Sandbox Secret
    define('MPESA_SHORTCODE', '174379'); // Sandbox Test Paybill
    define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');
    define('MPESA_OAUTH_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STK_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
} else {
    // Live Production Credentials (Fill when your Till/Paybill arrives)
    define('MPESA_CONSUMER_KEY', 'YOUR_LIVE_CONSUMER_KEY');
    define('MPESA_CONSUMER_SECRET', 'YOUR_LIVE_CONSUMER_SECRET');
    define('MPESA_SHORTCODE', 'YOUR_LIVE_TILL_OR_PAYBILL');
    define('MPESA_PASSKEY', 'YOUR_LIVE_PASSKEY');
    define('MPESA_OAUTH_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STK_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
}

// Callback URL (Where Safaricom sends payment results)
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/callback.php');