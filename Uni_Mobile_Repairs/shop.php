<?php
// shop.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Handle Add to Cart action from Shop page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    verify_csrf();
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if ($product_id > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add or increment product quantity in cart
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
        $_SESSION['flash_success'] = "Item added to cart!";
    }
    
    // Refresh page to prevent resubmission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Fetch categories for sidebar
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Handle Filters (Category & Search)
$selected_cat = intval($_GET['cat'] ?? 0);
$search = trim($_GET['search'] ?? '');

$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($selected_cat > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $selected_cat;
}

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY p.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$flash_msg = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Electronics & Accessories | UNI MOBILE REPAIRS</title>
    <link rel="stylesheet" href="Assets/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; margin: 0; }
        
        /* Navbar */
        .navbar { background: #0f172a; color: #fff; padding: 15px 5%; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .logo { font-size: 1.4rem; font-weight: bold; color: #38bdf8; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .nav-links { display: flex; align-items: center; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links a { color: #f8fafc; text-decoration: none; font-weight: 500; font-size: 0.95rem; }
        .nav-links a:hover { color: #38bdf8; }
        
        .search-bar { display: flex; background: #fff; border-radius: 6px; overflow: hidden; max-width: 400px; width: 100%; }
        .search-bar input { border: none; padding: 10px 15px; width: 100%; outline: none; }
        .search-bar button { background: #0284c7; border: none; color: #fff; padding: 0 15px; cursor: pointer; }

        /* Main Layout */
        .shop-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: 240px 1fr; gap: 30px; }
        
        /* Sidebar Filter */
        .sidebar { background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); height: fit-content; }
        .sidebar h3 { font-size: 1.1rem; margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; color: #1e293b; }
        .cat-list { list-style: none; padding: 0; margin: 0; }
        .cat-list li { margin-bottom: 8px; }
        .cat-list a { text-decoration: none; color: #475569; font-size: 0.95rem; display: block; padding: 8px 12px; border-radius: 6px; transition: background 0.2s; }
        .cat-list a:hover, .cat-list a.active { background: #e0f2fe; color: #0284c7; font-weight: 600; }

        /* Product Grid */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .product-card { background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.04); display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #e2e8f0; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        
        /* Fixed Image Frame */
        .img-container { width: 100%; height: 180px; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid #f1f5f9; }
        .product-img { width: 100%; height: 100%; object-fit: cover; }
        .placeholder-icon { font-size: 2.5rem; color: #94a3b8; }

        .card-body { padding: 15px; display: flex; flex-direction: column; flex-grow: 1; }
        .cat-badge { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 5px; }
        .product-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 10px 0; line-height: 1.3; }
        .product-price { font-size: 1.15rem; font-weight: 800; color: #0284c7; margin-bottom: 15px; margin-top: auto; }
        
        .btn-add-cart { width: 100%; background: #0284c7; color: #ffffff; border: none; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; }
        .btn-add-cart:hover { background: #0369a1; }
        
        .badge-cart { background: #ef4444; color: #fff; font-size: 0.75rem; padding: 2px 6px; border-radius: 10px; margin-left: 4px; }
    </style>
</head>
<body>

<!-- Header / Navigation -->
<nav class="navbar">
    <a href="index.php" class="logo"><i class="fa-solid fa-wrench"></i> UNI MOBILE</a>
    
    <form action="shop.php" method="GET" class="search-bar">
        <?php if ($selected_cat > 0): ?>
            <input type="hidden" name="cat" value="<?php echo $selected_cat; ?>">
        <?php endif; ?>
        <input type="text" name="search" placeholder="Search phones, laptops, repairs..." value="<?php echo e($search); ?>">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
        <li><a href="shop.php" style="color: #38bdf8;"><i class="fa-solid fa-store"></i> Shop</a></li>
        <li><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart <?php if ($cart_count > 0): ?><span class="badge-cart"><?php echo $cart_count; ?></span><?php endif; ?></a></li>
        <li><a href="Admin/products.php"><i class="fa-solid fa-user-lock"></i> Admin</a></li>
    </ul>
</nav>

<div class="shop-container">

    <!-- Categories Sidebar -->
    <aside class="sidebar">
        <h3><i class="fa-solid fa-layer-group"></i> Categories</h3>
        <ul class="cat-list">
            <li>
                <a href="shop.php" class="<?php echo $selected_cat === 0 ? 'active' : ''; ?>">All Products</a>
            </li>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="shop.php?cat=<?php echo $cat['id']; ?>" class="<?php echo $selected_cat === $cat['id'] ? 'active' : ''; ?>">
                        <?php echo e($cat['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Main Content Grid -->
    <main>
        <?php if (!empty($flash_msg)): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <i class="fa-solid fa-circle-check"></i> <?php echo e($flash_msg); ?>
            </div>
        <?php endif; ?>

        <h2 style="margin-top: 0; color: #0f172a; font-size: 1.5rem; margin-bottom: 20px;">
            <?php echo !empty($search) ? 'Search Results for "' . e($search) . '"' : 'All Electronics & Accessories'; ?>
        </h2>

        <?php if (empty($products)): ?>
            <div style="background: #fff; padding: 40px; text-align: center; border-radius: 10px;">
                <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 10px;"></i>
                <h3 style="color: #64748b;">No products found</h3>
                <a href="shop.php" style="color: #0284c7; text-decoration: none; font-weight: bold;">Clear filters and try again</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <?php $imageUrl = get_product_image_url($p['image'], false); ?>
                    <div class="product-card">
                        
                        <div class="img-container">
                            <?php if ($imageUrl): ?>
                                <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($p['name']); ?>" class="product-img">
                            <?php else: ?>
                                <div class="placeholder-icon">
                                    <i class="fa-solid fa-mobile-screen"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <span class="cat-badge"><?php echo e($p['category_name'] ?? 'General'); ?></span>
                            <h3 class="product-title"><?php echo e($p['name']); ?></h3>
                            <div class="product-price">Ksh <?php echo number_format($p['price'], 2); ?></div>

                            <form action="shop.php" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="add_to_cart" class="btn-add-cart">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</div>

</body>
</html>