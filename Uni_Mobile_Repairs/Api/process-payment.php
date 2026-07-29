<?php
// process-payment.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';
require_once 'config/payments.php';
require_once 'includes/mpesa.php';

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}

$stk_response = null;

// Auto-trigger M-Pesa STK push if requested via form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trigger_stk'])) {
    $phone = trim($_POST['phone_number']);
    $stk_response = initiateStkPush($phone, $order['total_amount'], $order['id']);
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 650px; margin: 50px auto;">
    <div class="form-card" style="text-align: center;">
        <h2><i class="fas fa-credit-card"></i> Complete Payment</h2>
        <p style="color: var(--text-muted); margin-top: 5px;">Order <strong>#<?php echo $order['id']; ?></strong> • Amount: <strong>Ksh <?php echo number_format($order['total_amount']); ?></strong></p>

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 25px 0;">

        <?php if ($order['payment_method'] === 'mpesa'): ?>
            <!-- M-PESA STK PUSH INTERFACE -->
            <div style="text-align: left;">
                <h3 style="display: flex; align-items: center; gap: 10px; color: #28a745;">
                    <i class="fas fa-mobile-alt" style="font-size: 1.5rem;"></i> M-Pesa Express (STK Push)
                </h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin: 10px 0 20px;">
                    Enter your M-Pesa registered phone number below. A prompt will automatically pop up on your phone asking for your M-Pesa PIN.
                </p>

                <?php if ($stk_response): ?>
                    <?php if ($stk_response['status']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($stk_response['customer_message']); ?>
                            <br><br>
                            <small>Please check your phone, enter your PIN, and wait for confirmation.</small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($stk_response['message']); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <form action="process-payment.php?order_id=<?php echo $order['id']; ?>" method="POST">
                    <div class="form-group">
                        <label>M-Pesa Phone Number</label>
                        <input type="tel" name="phone_number" class="form-control" placeholder="0712345678 or 254712345678" required>
                    </div>
                    <button type="submit" name="trigger_stk" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 15px;">
                        <i class="fas fa-paper-plane"></i> Send M-Pesa Payment Prompt
                    </button>
                </form>
            </div>

        <?php elseif ($order['payment_method'] === 'paypal'): ?>
            <!-- PAYPAL INTEGRATION -->
            <div style="text-align: center;">
                <h3 style="margin-bottom: 15px;"><i class="fab fa-paypal" style="color: #003087;"></i> Pay with PayPal / Card</h3>
                <?php $usd_amount = round($order['total_amount'] * KSH_TO_USD_RATE, 2); ?>
                <p style="margin-bottom: 25px; color: var(--text-muted);">Equivalent USD Amount: <strong>$<?php echo number_format($usd_amount, 2); ?></strong></p>
                
                <!-- Container for PayPal JS SDK Buttons -->
                <div id="paypal-button-container"></div>
            </div>

            <!-- Include PayPal JS SDK -->
            <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>
            <script>
                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: { value: '<?php echo $usd_amount; ?>' },
                                description: 'UNI MOBILE Order #<?php echo $order['id']; ?>'
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
                            // Send transaction ID to backend
                            fetch('api/paypal-capture.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    order_id: <?php echo $order['id']; ?>,
                                    paypal_order_id: details.id
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if(data.success) {
                                    window.location.href = 'order-confirmation.php?order_id=<?php echo $order['id']; ?>';
                                } else {
                                    alert('Payment recording failed: ' + data.message);
                                }
                            });
                        });
                    }
                }).render('#paypal-button-container');
            </script>

        <?php else: ?>
            <!-- BANK TRANSFER OR MANUAL PAYMENT -->
            <div style="text-align: left;">
                <h3><i class="fas fa-university"></i> Bank Transfer Instructions</h3>
                <p style="margin-top: 10px; color: var(--text-muted);">Please transfer the total amount to our bank account below and include your Order ID (#<?php echo $order['id']; ?>) as the transaction reference:</p>
                
                <div style="background: var(--surface-color); padding: 15px; border-radius: 6px; margin: 20px 0;">
                    <p><strong>Bank Name:</strong> KCB Bank Kenya</p>
                    <p><strong>Account Name:</strong> UNI MOBILE REPAIRS LTD</p>
                    <p><strong>Account Number:</strong> 1234567890</p>
                    <p><strong>Branch:</strong> Eldoret Main Branch</p>
                </div>

                <a href="order-confirmation.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">I Have Completed Payment</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>