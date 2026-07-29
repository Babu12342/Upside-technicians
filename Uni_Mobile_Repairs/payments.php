<?php
// config/payments.php

// Environment mode: 'sandbox' or 'live'
define('PAYMENT_ENV', 'sandbox');

// --- SAFARICOM M-PESA DARAJA CONFIG ---
define('MPESA_CONSUMER_KEY', 'YOUR_DARAJA_CONSUMER_KEY');
define('MPESA_CONSUMER_SECRET', 'YOUR_DARAJA_CONSUMER_SECRET');
define('MPESA_SHORTCODE', '174379'); // Default Daraja Sandbox Lipa Na M-Pesa Shortcode
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'); // Sandbox Passkey
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/api/mpesa-callback.php'); // Must be publicly accessible HTTPS URL

// Daraja Endpoints
if (PAYMENT_ENV === 'live') {
    define('MPESA_AUTH_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STK_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
} else {
    define('MPESA_AUTH_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STK_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
}

// --- PAYPAL CONFIG ---
define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_SANDBOX_CLIENT_ID');
define('PAYPAL_SECRET', 'YOUR_PAYPAL_SANDBOX_SECRET');
define('PAYPAL_CURRENCY', 'USD'); // PayPal default (Convert Ksh to USD if using live Ksh conversion)
define('KSH_TO_USD_RATE', 0.0077); // Approx 1 Ksh = 0.0077 USD (Adjust as needed)
?>