<?php
// includes/mpesa.php
require_once __DIR__ . '/../config/payments.php';

/**
 * Standardize local phone numbers to 254XXXXXXXXX format
 */
function formatMpesaPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        return '254' . substr($phone, 1);
    } elseif (substr($phone, 0, 3) === '254') {
        return $phone;
    } elseif (strlen($phone) === 9) {
        return '254' . $phone;
    }
    return $phone;
}

/**
 * Generate OAuth Access Token from Safaricom Daraja API
 */
function getMpesaAccessToken() {
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    
    $ch = curl_init(MPESA_AUTH_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return false;
    }

    $result = json_decode($response);
    return $result->access_token ?? false;
}

/**
 * Trigger M-Pesa STK Push Request
 */
function initiateStkPush($phone, $amount, $order_id) {
    $accessToken = getMpesaAccessToken();
    if (!$accessToken) {
        return ['status' => false, 'message' => 'Failed to generate M-Pesa access token.'];
    }

    $formattedPhone = formatMpesaPhone($phone);
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => 'CustomerPayBillOnline',
        'Amount'            => round($amount),
        'PartyA'            => $formattedPhone,
        'PartyB'            => MPESA_SHORTCODE,
        'PhoneNumber'       => $formattedPhone,
        'CallBackURL'       => MPESA_CALLBACK_URL,
        'AccountReference'  => 'ORD-' . $order_id,
        'TransactionDesc'   => 'Payment for Order #' . $order_id
    ];

    $ch = curl_init(MPESA_STK_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['ResponseCode']) && $result['ResponseCode'] === '0') {
        return [
            'status'             => true,
            'checkout_request_id'=> $result['CheckoutRequestID'],
            'customer_message'   => $result['CustomerMessage']
        ];
    }

    return [
        'status'  => false,
        'message' => $result['errorMessage'] ?? ($result['ResponseDescription'] ?? 'STK Push failed.')
    ];
}
?>