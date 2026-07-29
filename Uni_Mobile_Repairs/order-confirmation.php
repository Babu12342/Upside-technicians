<?php
// order-confirmation.php
require_once 'includes/db.php';

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 700px; margin: 60px auto; text-align: center;">
    <div class="form-card">
        <i class="fas fa-check-circle" style="font-size: 4rem; color: #28a745; margin-bottom: 20px;"></i>
        <h1>Thank You for Your Order!</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; margin-top: 10px;">Your order has been placed successfully.</p>

        <div style="background: var(--surface-color); padding: 20px; border-radius: 8px; margin: 30px 0; text-align: left;">
            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
            <p style="margin-top: 10px;"><strong>Total Amount:</strong> Ksh <?php echo number_format($order['total_amount']); ?></p>
            <p style="margin-top: 10px;"><strong>Payment Method:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
            <p style="margin-top: 10px;"><strong>Payment Status:</strong> <span style="color: #e67e22; font-weight: 600;"><?php echo ucfirst($order['payment_status']); ?></span></p>
        </div>

        <?php if ($order['payment_method'] === 'mpesa'): ?>
            <div class="alert alert-success" style="text-align: left;">
                <h4><i class="fas fa-mobile-alt"></i> M-Pesa Payment Instructions</h4>
                <p style="margin-top: 10px;">Please complete payment using Till/Paybill number or wait for an M-Pesa STK push prompt on your phone.</p>
            </div>
        <?php endif; ?>

        <a href="shop.php" class="btn btn-primary" style="margin-top: 20px;"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>