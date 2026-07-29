<?php
// checkout.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success_message = '';
$error_message = '';

// Ensure cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit();
}

// Calculate totals and fetch cart products
$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    if (count($ids) > 0) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $product_id = $product['id'];
            $quantity = $_SESSION['cart'][$product_id];
            $subtotal = $product['price'] * $quantity;
            $total_amount += $subtotal;
            
            $cart_items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'M-Pesa');

    if (!empty($customer_name) && !empty($phone) && !empty($address)) {
        try {
            // Save Order to Database
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, address, total_amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
            $stmt->execute([$customer_name, $phone, $address, $total_amount, $payment_method]);
            $order_id = $pdo->lastInsertId();

            // Save Order Items
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $item_stmt->execute([$order_id, $item['product']['id'], $item['quantity'], $item['product']['price']]);
            }

            // --- TELEGRAM NOTIFICATION ---
            $telegram_message  = "🛒 *New Store Order - Uni Mobile* 🛒\n\n";
            $telegram_message .= "*Order ID:* #" . $order_id . "\n";
            $telegram_message .= "*Customer:* " . $customer_name . "\n";
            $telegram_message .= "*Phone:* " . $phone . "\n";
            $telegram_message .= "*Address:* " . $address . "\n";
            $telegram_message .= "*Total Amount:* KSh " . number_format($total_amount, 2) . "\n";
            $telegram_message .= "*Payment:* " . $payment_method . "\n\n*Items Ordered:*\n";
            
            foreach ($cart_items as $item) {
                $telegram_message .= "- " . $item['product']['name'] . " (x" . $item['quantity'] . ")\n";
            }

            $botToken = "8874653683:AAE4KpvDuTvHGabZTe5oIo9lW5oKpviqbIs"; 
            $chatId = "5232258264";
            
            $website = "https://api.telegram.org/bot" . $botToken;
            $params = [
                'chat_id' => $chatId,
                'text' => $telegram_message,
                'parse_mode' => 'Markdown'
            ];
            
            $ch = curl_init($website . '/sendMessage');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
            // -----------------------------

            // Clear Cart
            unset($_SESSION['cart']);
            
            $success_order = true;
        } catch (PDOException $e) {
            $error_message = "Database Error: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all required delivery fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - UNI MOBILE REPAIRS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .checkout-grid {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 30px;
            padding: 0 20px;
        }
        @media (max-width: 850px) {
            .checkout-grid { grid-template-columns: 1fr; }
        }
        .card-box {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            outline: none;
            font-size: 1rem;
            background: #f8fafc;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #0284c7;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1);
        }
        .order-btn {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #fff;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
            transition: transform 0.1s, box-shadow 0.2s;
        }
        .order-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(22, 163, 74, 0.35);
        }
    </style>
</head>
<body style="background: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0;">

    <?php include 'includes/header.php'; ?>

    <div style="max-width: 1100px; margin: 30px auto 0 auto; padding: 0 20px;">
        <a href="cart.php" style="color: #0284c7; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Cart
        </a>
    </div>

    <?php if (isset($success_order)): ?>
        <div style="max-width: 700px; margin: 60px auto; background: #fff; padding: 40px; border-radius: 16px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
            <div style="width: 70px; height: 70px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px auto;">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="color: #0f172a; margin-bottom: 10px;">Order Placed Successfully!</h2>
            <p style="color: #64748b; margin-bottom: 30px;">Thank you for shopping with Uni Mobile Repairs. Our team has received your order and sent a notification to our fulfillment system.</p>
            <a href="index.php" style="background: #0284c7; color: #fff; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 600;">Return to Home</a>
        </div>
    <?php else: ?>

        <div class="checkout-grid">
            <!-- Left Column: Customer & Delivery Details -->
            <div class="card-box">
                <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 20px; font-size: 1.4rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-shipping-fast" style="color: #0284c7;"></i> Delivery & Billing Details
                </h3>

                <?php if (!empty($error_message)): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label>Phone Number (M-Pesa) *</label>
                        <input type="text" name="phone" class="form-control" required placeholder="e.g., 0712345678">
                    </div>

                    <div class="form-group">
                        <label>Delivery Location / Address *</label>
                        <textarea name="address" rows="3" class="form-control" required placeholder="Enter your specific delivery location (e.g., Eldoret Town, Moi University, etc.)"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Payment Method</label>
                        <div style="padding: 12px 16px; border: 1.5px solid #16a34a; background: #f0fdf4; border-radius: 10px; color: #166534; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-mobile-screen-button" style="font-size: 1.2rem;"></i> M-Pesa Mobile Checkout
                        </div>
                        <input type="hidden" name="payment_method" value="M-Pesa">
                    </div>

                    <button type="submit" name="place_order" class="order-btn">
                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i> Place Order Now
                    </button>
                </form>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="card-box" style="height: fit-content;">
                <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 20px; font-size: 1.4rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-receipt" style="color: #0284c7;"></i> Order Summary
                </h3>

                <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; max-height: 300px; overflow-y: auto; padding-right: 5px;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                            <div>
                                <div style="font-weight: 600; color: #1e293b; font-size: 0.95rem;"><?php echo htmlspecialchars($item['product']['name']); ?></div>
                                <div style="color: #64748b; font-size: 0.85rem;">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div style="font-weight: 600; color: #0f172a;">KSh <?php echo number_format($item['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="border-top: 2px solid #e2e8f0; padding-top: 15px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; color: #64748b; margin-bottom: 8px;">
                        <span>Delivery Fee</span>
                        <span style="color: #16a34a; font-weight: 600;">FREE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700; color: #0f172a;">
                        <span>Total Amount</span>
                        <span style="color: #0284c7;">KSh <?php echo number_format($total_amount, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

</body>
</html>