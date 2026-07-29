<?php
// order_success.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    header('Location: index.php');
    exit;
}

// Fetch order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Fetch order items with product names
$stmt_items = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed | UNI MOBILE REPAIRS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-container {
            max-width: 650px;
            margin: 40px auto;
            background: #fff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        .icon-circle {
            width: 50px;
            height: 50px;
            background-color: #22c55e;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 15px auto;
        }
        .heading {
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .subheading {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 15px;
        }
        .alert-badge {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 12px 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .support-box {
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            background: #f8fafc;
            margin-bottom: 30px;
        }
        .support-title {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .support-text {
            color: #64748b;
            font-size: 13.5px;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        .support-list {
            list-style: disc;
            padding-left: 20px;
            margin: 0;
            color: #475569;
            font-size: 13.5px;
        }
        .support-list li {
            margin-bottom: 5px;
        }
        .order-summary-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .summary-table th {
            text-align: left;
            color: #64748b;
            font-weight: 500;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .summary-table td {
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-table .price-col {
            text-align: right;
        }
        .summary-table .qty-col {
            text-align: center;
        }
        .summary-row-bold {
            font-weight: 700;
            font-size: 15px;
        }
        .summary-row-bold td {
            color: #0284c7;
            border-bottom: none;
            padding-top: 18px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            color: #0F172A;
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="icon-circle">✓</div>
    
    <h1 class="heading">Order Received!</h1>
    
    <p class="subheading">
        Thank you <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>. Your order has been placed successfully.
    </p>

    <div class="alert-badge">
        <span>✈</span> Order details dispatched directly to store management!
    </div>

    <div class="support-box">
        <div class="support-title">
            <span>📞</span> Need Urgent Assistance or Inquiries?
        </div>
        <p class="support-text">
            Our team is processing your order right now. If you need to follow up or alter your order details:
        </p>
        <ul class="support-list">
            <li>Call or SMS us at: <a href="tel:0703449550" style="color:#0284c7; font-weight: 500;">0703449550</a></li>
            <li>Quote your Order ID: <strong><?php echo htmlspecialchars($order['id']); ?></strong></li>
        </ul>
    </div>

    <h2 class="order-summary-title">Order Summary</h2>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="qty-col">Qty</th>
                <th class="price-col">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td class="qty-col"><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td class="price-col">Ksh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="summary-row-bold">
                <td colspan="2">Total Paid/Due (Free Delivery)</td>
                <td class="price-col">Ksh <?php echo number_format($order['total_amount'], 2); ?></td>
            </tr>
        </tbody>
    </table>

    <a href="index.php" class="back-link">← Return to Homepage</a>
</div>

</body>
</html>