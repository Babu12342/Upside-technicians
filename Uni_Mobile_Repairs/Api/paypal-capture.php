<?php
// api/paypal-capture.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../config/payments.php';

$input = json_decode(file_get_contents('php://input'), true);
$order_id = intval($input['order_id'] ?? 0);
$paypal_order_id = trim($input['paypal_order_id'] ?? '');

if ($order_id <= 0 || empty($paypal_order_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order details provided.']);
    exit;
}

try {
    // Update DB with PayPal transaction confirmation
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'paid', 
            order_status = 'processing', 
            transaction_reference = ? 
        WHERE id = ?
    ");
    $stmt->execute(['PAYPAL-' . $paypal_order_id, $order_id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>