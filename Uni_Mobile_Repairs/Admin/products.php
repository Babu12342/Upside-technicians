<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #090d16; margin: 0; display: flex; color: #f8fafc; min-height: 100vh; }
        .sidebar { width: 260px; background: #182232; color: #fff; min-height: 100vh; padding: 28px 20px; box-sizing: border-box; border-right: 1px solid rgba(255, 255, 255, 0.06); }
        .sidebar h2 { font-size: 1.25rem; margin-bottom: 36px; color: #38bdf8; text-align: center; font-weight: 800; letter-spacing: 0.5px; }
        .sidebar a { display: flex; align-items: center; gap: 12px; color: #94a3b8; text-decoration: none; padding: 12px 18px; border-radius: 12px; margin-bottom: 8px; font-weight: 600; font-size: 0.92rem; transition: all 0.25s ease; }
        .sidebar a:hover, .sidebar a.active { background: rgba(56, 189, 248, 0.12); color: #38bdf8; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .card { background: #182232; padding: 32px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.06); box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.4); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); text-align: left; font-size: 0.92rem; }
        th { background: #111827; color: #38bdf8; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.8px; font-weight: 700; }
        td { color: #cbd5e1; }
        tr:hover td { background: rgba(56, 189, 248, 0.03); }
        .btn-action { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #fff; padding: 9px 18px; border-radius: 10px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.25s ease; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3); }
        .btn-action:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(14, 165, 233, 0.5); background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Uni Mobile Admin</h2>
    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="products.php" class="active"><i class="fas fa-boxes"></i> Products</a>
    <a href="orders.php"><i class="fas fa-box-open"></i> Manage Orders</a>
    <a href="repairs.php"><i class="fas fa-tools"></i> Manage Repairs</a>
    <a href="logout.php" style="margin-top: 40px; color: #f43f5e;"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h1 style="margin: 0; font-size: 1.8rem; color: #f8fafc; font-weight: 800;">Product Inventory</h1>
        <span style="font-weight: 600; color: #94a3b8; background: #182232; padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">Welcome, Admin</span>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #f8fafc;">All Products</h3>
            <a href="product-add.php" class="btn-action"><i class="fas fa-plus"></i> Add New Product</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 40px;">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><strong style="color: #38bdf8;">#<?php echo $p['id']; ?></strong></td>
                            <td><strong style="color: #f8fafc;"><?php echo htmlspecialchars($p['name'] ?? ''); ?></strong></td>
                            <td><strong style="color: #34d399;">KES <?php echo number_format($p['price'] ?? 0, 2); ?></strong></td>
                            <td><a href="#" class="btn-action" style="padding: 6px 14px; font-size: 0.8rem;">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>