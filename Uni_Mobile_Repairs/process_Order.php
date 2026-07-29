<?php
// process_order.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mpesa_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        header('Location: cart.php');
        exit;
    }

    // Sanitize user input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $phone_raw  = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $payment    = trim($_POST['payment_method'] ?? 'mpesa');

    $customer_name = trim($first_name . ' ' . $last_name);
    $formatted_phone = format_phone_number($phone_raw);

    // Calculate subtotal and items
    $subtotal = 0;
    $product_ids = array_keys($cart);
    $items = [];

    if (!empty($product_ids)) {
        $in = str_repeat('?,', count($product_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $qty = $cart[$p['id']] ?? 0;
            $subtotal += $p['price'] * $qty;
            $items[] = [
                'name'       => $p['name'],
                'product_id' => $p['id'],
                'price'      => $p['price'],
                'quantity'   => $qty
            ];
        }
    }

    $shipping_fee = 0; // Free delivery
    $total_amount = $subtotal + $shipping_fee;

    try {
        $pdo->beginTransaction();

        // 1. Insert Order Record
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, email, address, total_amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending Payment', NOW())");
        $stmt->execute([$customer_name, $formatted_phone, $email, $address, $total_amount, $payment]);
        $order_id = $pdo->lastInsertId();

        // 2. Insert Order Items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt_item->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        $pdo->commit();

        // --- 3. TRIGGER SAFARICOM M-PESA STK PUSH ---
        if ($payment === 'mpesa') {
            $access_token = get_mpesa_access_token();

            if ($access_token) {
                $timestamp = date('YmdHis');
                $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

                $stkPayload = [
                    'BusinessShortCode' => MPESA_SHORTCODE,
                    'Password'          => $password,
                    'Timestamp'         => $timestamp,
                    'TransactionType'   => 'CustomerPayBillOnline',
                    'Amount'            => (int)$total_amount,
                    'PartyA'            => $formatted_phone,
                    'PartyB'            => MPESA_SHORTCODE,
                    'PhoneNumber'       => $formatted_phone,
                    'CallBackURL'       => MPESA_CALLBACK_URL,
                    'AccountReference'  => "UNI_MOBILE_ORDER_" . $order_id,
                    'TransactionDesc'   => "Payment for Order #" . $order_id
                ];

                $ch = curl_init(MPESA_STK_URL);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $access_token,
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPayload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $stkResponse = curl_exec($ch);
                curl_close($ch);
            }
        }

        // --- 4. AUTOMATIC TELEGRAM NOTIFICATION TO STORE ADMIN ---
        $telegram_bot_token = "8874653683:AAE4KpvDuTvHGabZTe5oIo9lW5oKpviqbIs";
        $telegram_chat_id   = "5232258264";

        $tg_msg  = "🚨 *NEW ORDER #{$order_id} RECEIVED!*\n\n";
        $tg_msg .= "👤 *Customer:* {$customer_name}\n";
        $tg_msg .= "📞 *Phone:* {$formatted_phone}\n";
        $tg_msg .= "📍 *Location:* {$address}\n";
        $tg_msg .= "💳 *Payment Method:* " . strtoupper($payment) . "\n\n";
        $tg_msg .= "🛒 *ITEMS:*\n";

        foreach ($items as $item) {
            $item_total = number_format($item['price'] * $item['quantity'], 2);
            $tg_msg .= "• {$item['name']} (x{$item['quantity']}): Ksh {$item_total}\n";
        }

        $tg_msg .= "\n📦 *Delivery:* FREE";
        $tg_msg .= "\n💰 *TOTAL AMOUNT:* Ksh " . number_format($total_amount, 2);

        $apiUrl = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
        $postFields = [
            'chat_id'    => $telegram_chat_id,
            'text'       => $tg_msg,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_exec($ch);
        curl_close($ch);

        // Clear cart session
        unset($_SESSION['cart']);

        // Redirect customer directly to receipt page
        header("Location: order_success.php?order_id={$order_id}");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        $_SESSION['flash_error'] = "Order Error: " . $e->getMessage();
        header('Location: checkout.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}