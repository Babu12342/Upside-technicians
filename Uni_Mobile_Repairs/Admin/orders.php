<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle Status Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $new_status = trim($_POST['status']);
    
    $allowed_statuses = ['Pending Payment', 'Processing', 'Out for Delivery', 'Completed', 'Cancelled'];
    if ($order_id && in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        header("Location: orders.php?success=1");
        exit;
    }
}

$stmt = $pdo->query("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items FROM orders o ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #090d16;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        .admin-container {
            max-width: 1280px;
            margin: 40px auto;
            padding: 0 24px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            background: #182232;
            padding: 24px 32px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.4);
        }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-links a:hover { color: #7dd3fc; text-decoration: underline; }
        .admin-card {
            background: #182232;
            padding: 32px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.4);
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .orders-table th {
            text-align: left;
            background: #111827;
            color: #38bdf8;
            padding: 16px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #334155;
        }
        .orders-table td {
            padding: 18px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            vertical-align: middle;
            color: #cbd5e1;
        }
        .orders-table tr:hover td { background: rgba(56, 189, 248, 0.03); }
        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.3px;
        }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-processing { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); }
        .status-completed { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-cancelled { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
        
        select.status-select {
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #334155;
            background: #111827;
            color: #f8fafc;
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }
        .btn-update {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #fff;
            border: none;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
        }
        .btn-update:hover { 
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <h1 style="font-size: 24px; color: #f8fafc; margin: 0; font-weight: 800;">📦 Store Orders Dashboard</h1>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Dashboard Home</a>
            <a href="products.php"><i class="fas fa-boxes"></i> Inventory</a>
            <a href="product-add.php"><i class="fas fa-plus"></i> Add Product</a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 14px 20px; border-radius: 14px; margin-bottom: 24px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-check-circle"></i> Order status updated successfully!
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Phone / Location</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #94a3b8; padding: 40px;">No orders found yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): 
                        $status_class = match($ord['status']) {
                            'Pending Payment' => 'status-pending',
                            'Processing' => 'status-processing',
                            'Completed' => 'status-completed',
                            default => 'status-cancelled'
                        };
                    ?>
                        <tr>
                            <td><strong style="color: #38bdf8;">#<?php echo $ord['id']; ?></strong></td>
                            <td>
                                <strong style="color: #f8fafc;"><?php echo htmlspecialchars($ord['customer_name']); ?></strong><br>
                                <span style="font-size: 12px; color: #94a3b8;"><?php echo htmlspecialchars($ord['email']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($ord['phone']); ?><br>
                                <span style="font-size: 12px; color: #94a3b8;"><?php echo htmlspecialchars($ord['address']); ?></span>
                            </td>
                            <td><span style="font-weight: 600; color: #38bdf8;"><?php echo $ord['total_items']; ?> items</span></td>
                            <td><strong style="color: #f8fafc;">Ksh <?php echo number_format($ord['total_amount'], 2); ?></strong></td>
                            <td><span style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: #94a3b8;"><?php echo htmlspecialchars($ord['payment_method']); ?></span></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($ord['status']); ?>
                                </span>
                            </td>
                            <td style="font-size: 13px; color: #94a3b8;"><?php echo $ord['created_at']; ?></td>
                            <td>
                                <form action="orders.php" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                    <select name="status" class="status-select">
                                        <option value="Pending Payment" <?php if($ord['status']=='Pending Payment') echo 'selected'; ?>>Pending</option>
                                        <option value="Processing" <?php if($ord['status']=='Processing') echo 'selected'; ?>>Processing</option>
                                        <option value="Completed" <?php if($ord['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                        <option value="Cancelled" <?php if($ord['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn-update">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>