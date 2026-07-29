<?php
// api/mpesa-callback.php
header("Content-Type: application/json");
require_once '../includes/db.php';

// Log incoming raw callback for debugging
$callbackJSON = file_get_contents('php://input');
$logFile = "../logs/mpesa_callback.log";

if (!is_dir('../logs')) {
    mkdir('../logs', 0777, true);
}
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $callbackJSON . PHP_EOL, FILE_APPEND);

$data = json_decode($callbackJSON, true);

if (!$data || !isset($data['Body']['stkCallback'])) {
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Invalid Payload"]);
    exit;
}

$stkCallback = $data['Body']['stkCallback'];
$resultCode  = $stkCallback['ResultCode'];
$resultDesc  = $stkCallback['ResultDesc'];
$checkoutReqId = $stkCallback['CheckoutRequestID'];

if ($resultCode == 0) {
    // Payment Successful
    $amount = 0;
    $mpesaReceiptNumber = '';
    $phone = '';

    if (isset($stkCallback['CallbackMetadata']['Item'])) {
        foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
            if ($item['Name'] === 'Amount') $amount = $item['Value'];
            if ($item['Name'] === 'MpesaReceiptNumber') $mpesaReceiptNumber = $item['Value'];
            if ($item['Name'] === 'PhoneNumber') $phone = $item['Value'];
        }
    }

    try {
        // Update order status in DB based on checkout_request_id or latest pending order
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'paid', 
                order_status = 'processing',
                transaction_reference = ? 
            WHERE payment_method = 'mpesa' AND payment_status = 'pending'
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$mpesaReceiptNumber]);

    } catch (Exception $e) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] DB Update Error: ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}

// Acknowledge Safaricom Daraja API
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Callback Received Successfully"]);
?>