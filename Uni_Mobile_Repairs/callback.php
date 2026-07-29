<?php
// callback.php
require_once __DIR__ . '/includes/db.php';

// Receive Safaricom JSON Response
$callbackJSON = file_get_contents('php://input');
$logFile = "mpesa_callback.log";
file_put_contents($logFile, $callbackJSON . PHP_EOL, FILE_APPEND);

$data = json_decode($callbackJSON, true);

if (isset($data['Body']['stkCallback'])) {
    $stkCallback = $data['Body']['stkCallback'];
    $resultCode = $stkCallback['ResultCode'];
    
    // Extract AccountReference / Order ID
    if ($resultCode == 0) {
        $metaItems = $stkCallback['CallbackMetadata']['Item'];
        $mpesaReceiptNumber = '';
        
        foreach ($metaItems as $item) {
            if ($item['Name'] === 'MpesaReceiptNumber') {
                $mpesaReceiptNumber = $item['Value'];
            }
        }

        // Payment successful -> Update order status in DB
        // Add additional logic here to match order ID and set status = 'Paid'
    }
}

// Reply to Safaricom
header("Content-Type: application/json");
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);