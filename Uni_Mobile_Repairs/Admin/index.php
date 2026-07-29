<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Fetch dashboard metrics
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Completed'")->fetchColumn() ?: 0;
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending Payment'")->fetchColumn();

// Fetch recent orders
$stmt = $pdo->query("
    SELECT o.*, 
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items 
    FROM orders o 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Upside Technicians</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #090d16;
            color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
            flex-wrap: wrap;
            gap: 16px;
            background: linear-gradient(135deg, #182232 100%, #111827 0%);
            padding: 24px 32px;
            border-radius: 20px;
            border: 1px solid rgba(56, 189, 248, 0.15);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }
        .nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-links a {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
        }
        .nav-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(14, 165, 233, 0.5);
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        }
        .btn-logout {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important;
            box-shadow: 0 4px 14px rgba(244, 63, 94, 0.3) !important;
        }
        .btn-logout:hover {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
            box-shadow: 0 8px 22px rgba(244, 63, 94, 0.5) !important;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
        .stat-card {
            background: #182232;
            padding: 28px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(56, 189, 248, 0.3);
        }
        .stat-title {
            font-size: 13px;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 34px;
            font-weight: 800;
            color: #f8fafc;
        }
        .admin-card {
            background: #182232;
            padding: 32px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.4);
        }
        .table-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .table-title {
            font-size: 20px;
            font-weight: 700;
            color: #f8fafc;
        }
        .view-all-link {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.2s;
        }
        .view-all-link:hover { color: #7dd3fc; text-decoration: underline; }
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
            text-align: center;
            letter-spacing: 0.3px;
        }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-processing { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); }
        .status-completed { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-cancelled { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <h1 style="font-size: 26px; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 12px;">
            <span style="background: rgba(56, 189, 248, 0.15); padding: 8px 12px; border-radius: 12px; font-size: 22px;">🛠️</span> Admin Control Panel
        </h1>
        <div class="nav-links">
            <a href="orders.php"><i class="fas fa-box-open"></i> Manage Orders</a>
            <a href="repairs.php"><i class="fas fa-tools"></i> Manage Repairs</a>
            <a href="products.php"><i class="fas fa-boxes"></i> Manage Inventory</a>
            <a href="product-add.php"><i class="fas fa-plus"></i> Add Product</a>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($total_orders); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Completed Revenue</div>
            <div class="stat-value" style="color: #34d399;">Ksh <?php echo number_format($total_revenue, 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Pending Orders</div>
            <div class="stat-value" style="color: #fbbf24;"><?php echo number_format($pending_orders); ?></div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="admin-card">
        <div class="table-header-flex">
            <div class="table-title">Recent Orders</div>
            <a href="orders.php" class="view-all-link">View All Orders →</a>
        </div>
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
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 40px;">No recent orders found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_orders as $ord): 
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
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>