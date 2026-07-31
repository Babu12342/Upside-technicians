<?php
// index.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Direct Add to Cart Handler (Keeps user on index.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    verify_csrf();
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if ($product_id > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
        $_SESSION['flash_success'] = "Item added to cart!";
    }
    
    header('Location: index.php');
    exit;
}

// Fetch Products for Homepage Showcase Grid
try {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 4");
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
    <title>UPSIDE TECHNICIANS | Electronics & Service</title>
    <link rel="stylesheet" href="Assets/CSS/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #f1f5f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #1e293b; 
            margin: 0; 
            padding: 0; 
        }
        
        /* --- TOP NAVBAR --- */
        .navbar { 
            background: #0b1320; 
            color: #fff; 
            padding: 12px 5%; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            gap: 15px; 
        }
        .logo { 
            font-size: 1.35rem; 
            font-weight: 800; 
            color: #00a8ff; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            letter-spacing: 0.5px; 
        }
        .logo i { color: #fff; font-size: 1.2rem; }
        
        .search-bar { 
            display: flex; 
            background: #fff; 
            border-radius: 6px; 
            overflow: hidden; 
            max-width: 400px; 
            width: 100%; 
        }
        .search-bar input { 
            border: none; 
            padding: 8px 14px; 
            width: 100%; 
            outline: none; 
            font-size: 0.88rem; 
            color: #333;
        }
        .search-bar button { 
            background: #00a8ff; 
            border: none; 
            color: #fff; 
            padding: 0 14px; 
            cursor: pointer; 
        }

        .nav-links { 
            display: flex; 
            align-items: center; 
            gap: 18px; 
            list-style: none; 
            margin: 0; 
            padding: 0; 
        }
        .nav-links a { 
            color: #f8fafc; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 0.9rem; 
            display: flex; 
            align-items: center; 
            gap: 6px; 
            transition: color 0.2s;
        }
        .nav-links a:hover, .nav-links a.active { color: #00a8ff; }

        .cart-pill {
            background: rgba(255, 255, 255, 0.15);
            padding: 5px 12px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .badge-cart { 
            background: #ef4444; 
            color: #fff; 
            font-size: 0.72rem; 
            font-weight: 700;
            padding: 2px 7px; 
            border-radius: 10px; 
        }

        /* --- HERO BANNER --- */
        .hero-banner { 
            background: linear-gradient(rgba(11, 19, 32, 0.82), rgba(11, 19, 32, 0.88)), url('https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1350&q=80') center/cover no-repeat;
            color: #fff; 
            text-align: center; 
            padding: 75px 20px 85px 20px; 
            border-bottom: 2px solid #00a8ff;
        }
        .hero-banner h1 { 
            font-size: 2.4rem; 
            margin: 0 0 12px 0; 
            font-weight: 800; 
            letter-spacing: -0.5px; 
        }
        .hero-subtitle { 
            font-size: 0.98rem; 
            color: #cbd5e1; 
            max-width: 650px; 
            margin: 0 auto 26px auto; 
            line-height: 1.5; 
        }
        .hero-actions { 
            display: flex; 
            justify-content: center; 
            gap: 12px; 
        }
        .btn-shop-now { 
            background: #00a8ff; 
            color: #fff; 
            padding: 10px 22px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.9rem; 
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s; 
        }
        .btn-shop-now:hover { background: #0088cc; }
        
        .btn-book-repair { 
            background: rgba(255, 255, 255, 0.2); 
            color: #ffffff; 
            padding: 10px 22px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.9rem; 
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s; 
        }
        .btn-book-repair:hover { background: rgba(255, 255, 255, 0.3); }

        /* --- MAIN CONTENT & PRODUCT GRID --- */
        .main-container { 
            max-width: 1150px; 
            margin: 40px auto 60px auto; 
            padding: 0 20px; 
        }
        .section-heading { 
            font-size: 1.4rem; 
            color: #0f172a; 
            margin-bottom: 25px; 
            font-weight: 800; 
        }

        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
            gap: 20px; 
        }

        .product-card { 
            background: #ffffff; 
            border-radius: 10px; 
            padding: 16px; 
            box-shadow: 0 1px 4px rgba(0,0,0,0.05); 
            display: flex; 
            flex-direction: column; 
            transition: transform 0.2s, box-shadow 0.2s; 
        }
        .product-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 6px 16px rgba(0,0,0,0.08); 
        }
        
        .img-container { 
            width: 100%; 
            height: 160px; 
            background: #f8fafc; 
            border-radius: 6px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden; 
            margin-bottom: 12px; 
            padding: 8px; 
            box-sizing: border-box; 
        }
        .product-img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .placeholder-icon { font-size: 2.5rem; color: #cbd5e1; }

        .card-body { display: flex; flex-direction: column; flex-grow: 1; }
        .cat-badge { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 4px; }
        .product-title { font-size: 0.98rem; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
        .product-price { font-size: 1.1rem; font-weight: 800; color: #00a8ff; margin-bottom: 14px; margin-top: auto; }
        
        .btn-add-cart { 
            width: 100%; 
            background: #00a8ff; 
            color: #ffffff; 
            border: none; 
            padding: 9px; 
            border-radius: 6px; 
            font-weight: 700; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 7px; 
            font-size: 0.88rem; 
            transition: background 0.2s; 
        }
        .btn-add-cart:hover { background: #0088cc; }

        /* --- FOOTER STYLES --- */
        .site-footer {
            background-color: #06131e;
            color: #ede7dc;
            padding: 50px 0 25px 0;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1150px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 35px;
        }

        .footer-logo {
            font-size: 1.6rem;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #ede7dc;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .footer-logo i {
            font-size: 1.5rem;
            transform: rotate(-10deg);
        }

        .footer-text {
            font-size: 0.92rem;
            line-height: 1.5;
            color: #ede7dc;
            margin-bottom: 22px;
            max-width: 310px;
            opacity: 0.9;
        }

        .social-icons {
            display: flex;
            gap: 10px;
        }

        .social-btn {
            width: 36px;
            height: 36px;
            background-color: #ede7dc;
            color: #06131e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.95rem;
            transition: transform 0.2s, opacity 0.2s;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .footer-col h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ede7dc;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .footer-links, .contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #ede7dc;
            text-decoration: none;
            font-size: 0.93rem;
            opacity: 0.9;
            transition: opacity 0.2s;
        }

        .footer-links a:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .footer-links a.highlight-link {
            text-decoration: underline;
            font-weight: 600;
        }

        .contact-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.93rem;
            margin-bottom: 16px;
            color: #ede7dc;
            opacity: 0.9;
        }

        .contact-list i {
            font-size: 1rem;
            width: 18px;
            text-align: center;
        }

        .footer-divider {
            border: none;
            border-top: 1px solid rgba(237, 231, 220, 0.15);
            margin: 30px 0 20px 0;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
            color: #ede7dc;
            opacity: 0.85;
        }

        .footer-bottom p {
            margin: 0;
        }

        @media (max-width: 900px) {
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 550px) {
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<nav class="navbar">
    <a href="index.php" class="logo">
        <i class="fa-solid fa-wrench"></i> UPSIDE TECHNICIANS
    </a>
    
    <form action="shop.php" method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search phones, laptops, repairs...">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <ul class="nav-links">
        <li><a href="index.php" class="active"><i class="fa-solid fa-house"></i> Home</a></li>
        <li><a href="shop.php"><i class="fa-solid fa-store"></i> Shop</a></li>
        <li><a href="book-repair.php"><i class="fa-solid fa-screwdriver-wrench"></i> Book Repair</a></li>
        <li>
            <a href="cart.php" class="cart-pill">
                <i class="fa-solid fa-cart-shopping"></i> Cart <span class="badge-cart"><?php echo $cart_count; ?></span>
            </a>
        </li>
        <li><a href="Admin/products.php"><i class="fa-solid fa-user"></i> Admin</a></li>
    </ul>
</nav>

<!-- Hero Banner -->
<section class="hero-banner">
    <h1>Expert Repairs & Premium Tech</h1>
    <p class="hero-subtitle">We fix your broken screens, battery issues, and sell authentic electronics. Tech on Wheels.</p>
    <div class="hero-actions">
        <a href="shop.php" class="btn-shop-now"><i class="fa-solid fa-cart-shopping"></i> Shop Now</a>
        <a href="book-repair.php" class="btn-book-repair"><i class="fa-solid fa-screwdriver-wrench"></i> Book Repair</a>
    </div>
</section>

<!-- Main Container -->
<div class="main-container">

    <?php if (!empty($flash_msg)): ?>
        <div style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check"></i> <?php echo e($flash_msg); ?>
        </div>
    <?php endif; ?>

    <h2 class="section-heading">Latest Arrivals</h2>

    <!-- Product Grid -->
    <?php if (empty($products)): ?>
        <div style="background: #fff; padding: 40px; text-align: center; border-radius: 8px;">
            <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 10px;"></i>
            <h3 style="color: #64748b;">No products available at the moment</h3>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <?php $imageUrl = get_product_image_url($p['image']); ?>
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
                        <span class="cat-badge"><?php echo e($p['category_name'] ?? 'Electronics'); ?></span>
                        <h3 class="product-title"><?php echo e($p['name']); ?></h3>
                        <div class="product-price">Ksh <?php echo number_format($p['price'], 2); ?></div>

                        <form action="index.php" method="POST" style="margin: 0;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn-add-cart">
                                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
                            </button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- FOOTER SECTION -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-grid">
            
            <!-- Brand & Socials -->
            <div class="footer-col brand-col">
                <div class="footer-logo">
                    <i class="fa-solid fa-wrench"></i> UPSIDE TECHNICIANS
                </div>
                <p class="footer-text">
                    Your trusted campus hub for certified device repairs, authentic electronics, and premium tech accessories.
                </p>
                <div class="social-icons">
                    <a href="https://www.facebook.com/profile.php?id=61564467094403" target="_blank" rel="noopener noreferrer" class="social-btn" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://wa.me/message/C2BYVXRRSCCDB1" target="_blank" rel="noopener noreferrer" class="social-btn" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="https://www.instagram.com/upsidetechnicians" target="_blank" rel="noopener noreferrer" class="social-btn" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://youtube.com/@gamingcraftandrepairs?si=DFCtNMM-xoHjtZzo" target="_blank" rel="noopener noreferrer" class="social-btn" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <!-- Customer Services -->
            <div class="footer-col">
                <h4>Customer Services</h4>
                <ul class="footer-links">
                    <li><a href="tel:0703449550" onclick="alert('Upside Technicians Help Center:\n\nCall or WhatsApp us directly at 0703449550 for instant support and repair inquiries.');">Help Center</a></li>
                    <li><a href="cart.php">Order Status</a></li>
                    <li><a href="book-repair.php" class="highlight-link">Book a Repair</a></li>
                    <li><a href="#" onclick="alert('Upside Technicians Return Policy:\n\n1. 7-Day Exchange: Items with manufacturing defects can be returned or exchanged within 7 days of delivery');">Return Policy</a></li>
                    <li><a href="book-repair.php">Repairs</a></li>
                </ul>
            </div>

            <!-- Information -->
            <div class="footer-col">
                <h4>Information</h4>
                <ul class="footer-links">
                    <li><a href="#" onclick="alert('About Upside Technicians:\n\nWe are your trusted campus tech partner specializing in expert smartphone, tablet, and laptop repairs, genuine spare parts, and device support.');">About Us</a></li>
                    <li><a href="#" onclick="alert('Privacy Policy:\n\nUpside Technicians respects your personal data. Customer contact details and device information collected during orders or bookings are only used to fulfil services.');">Privacy Policy</a></li>
                    <li><a href="#" onclick="alert('Terms & Conditions:\n\n1. Warranty applies only to fixed issues specified on the repair invoice.\n2. Payment is due upon completion of repair or at checkout.');">Terms & Conditions</a></li>
                </ul>
            </div>

            <!-- Contact Us -->
            <div class="footer-col">
                <h4>Contact Us</h4>
                <ul class="contact-list">
                    <li><i class="fa-solid fa-location-dot"></i> Eldoret, Kenya</li>
                    <li><i class="fa-solid fa-phone"></i> <a href="tel:0703449550" style="color: inherit; text-decoration: none;">+254 703 449 550</a></li>
                    <li><i class="fa-solid fa-envelope"></i> <a href="mailto:gamingcraftandrepairs@gmail.com" style="color: inherit; text-decoration: none; word-break: break-all;">gamingcraftandrepairs@gmail.com</a></li>
                </ul>
            </div>

        </div>

        <hr class="footer-divider">

        <div class="footer-bottom">
            <p>Copyright © 2026 Upside Technicians. All Rights Reserved.</p>
            <span class="lang-select">English</span>
        </div>
    </div>
</footer>

</body>
</html>
