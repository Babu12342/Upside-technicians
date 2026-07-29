<?php
// includes/header.php
?>
<head>
    <!-- Favicon / Tab Icon -->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon.png">
    <link rel="apple-touch-icon" href="assets/favicon.png">
</head>

<nav style="background: #0f172a; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <div style="font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
        <a href="index.php" style="color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-tools" style="color: #0284c7;"></i> UPSIDE TECHNICIANS
        </a>
    </div>
    
    <!-- Search Bar -->
    <div style="flex: 0 1 400px; position: relative;">
        <form action="shop.php" method="GET" style="display: flex;">
            <input type="text" name="search" placeholder="Search phones, laptops, repairs..." style="width: 100%; padding: 8px 15px; border-radius: 6px; border: none; background: #1e293b; color: #fff; outline: none; font-size: 0.9rem;">
            <button type="submit" style="background: transparent; border: none; color: #94a3b8; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Nav Links -->
    <ul style="list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; align-items: center; font-size: 0.95rem;">
        <li><a href="index.php" style="color: #94a3b8; text-decoration: none; transition: 0.2s;"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="shop.php" style="color: #94a3b8; text-decoration: none; transition: 0.2s;"><i class="fas fa-store"></i> Shop</a></li>
        <li><a href="book_repair.php" style="color: #94a3b8; text-decoration: none; transition: 0.2s;"><i class="fas fa-tools"></i> Book Repair</a></li>
        <li><a href="cart.php" style="color: #94a3b8; text-decoration: none; transition: 0.2s;"><i class="fas fa-shopping-cart"></i> Cart <span style="background: #0284c7; color: #fff; padding: 2px 6px; border-radius: 10px; font-size: 0.75rem;"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span></a></li>
        <li><a href="admin/index.php" style="color: #94a3b8; text-decoration: none; transition: 0.2s;"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
    </ul>
</nav>
