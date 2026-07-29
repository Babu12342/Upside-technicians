<?php
// cart.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Handle quantity updates or item removal
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = intval($_GET['id'] ?? 0);

    if ($action === 'remove' && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    } elseif ($action === 'update' && isset($_SESSION['cart'][$product_id])) {
        $qty = intval($_POST['quantity'] ?? 1);
        if ($qty > 0) {
            $_SESSION['cart'][$product_id] = $qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("Location: cart.php");
    exit();
}

$cart_items = [];
$total_amount = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Upside Technicians</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table-custom th, .table-custom td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-custom th {
            color: #1e293b;
            font-weight: 600;
            background: #f8fafc;
        }
        .checkout-btn {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
            transition: transform 0.1s, box-shadow 0.2s;
        }
        .checkout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(22, 163, 74, 0.35);
        }
        .back-link {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #7dd3fc;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; flex-direction: column;">

    <?php include 'includes/header.php'; ?>

    <div style="flex: 1; padding: 20px;">
        <div style="max-width: 900px; margin: 20px auto 0 auto;">
            <a href="shop.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>

        <div class="cart-container">
            <h2 style="color: #0f172a; margin-top: 0; margin-bottom: 25px; font-size: 1.8rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-shopping-cart" style="color: #0284c7;"></i> Your Shopping Cart
            </h2>

            <?php if (empty($cart_items)): ?>
                <div style="text-align: center; padding: 40px 0;">
                    <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 20px;">Your cart is currently empty.</p>
                    <a href="shop.php" class="checkout-btn" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <i class="fas fa-store"></i> Browse Products
                    </a>
                </div>
            <?php else: ?>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td style="font-weight: 600; color: #1e293b;">
                                    <?php echo htmlspecialchars($item['product']['name']); ?>
                                </td>
                                <td style="color: #475569;">KSh <?php echo number_format($item['product']['price'], 2); ?></td>
                                <td>
                                    <form action="cart.php?action=update&id=<?php echo $item['product']['id']; ?>" method="POST" style="display: flex; align-items: center; gap: 8px;">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px; padding: 6px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                        <button type="submit" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" title="Update Quantity"><i class="fas fa-sync-alt"></i></button>
                                    </form>
                                </td>
                                <td style="font-weight: 600; color: #0f172a;">KSh <?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <a href="cart.php?action=remove&id=<?php echo $item['product']['id']; ?>" style="color: #ef4444; text-decoration: none; font-size: 1.1rem;" title="Remove Item"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #e2e8f0; padding-top: 25px; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <div style="color: #64748b; font-size: 0.95rem;">Total Amount Due:</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #0f172a;">KSh <?php echo number_format($total_amount, 2); ?></div>
                    </div>
                    <div>
                        <a href="checkout.php" class="checkout-btn">
                            Proceed to Checkout <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>