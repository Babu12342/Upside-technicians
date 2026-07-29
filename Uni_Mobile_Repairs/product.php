<?php
// product.php
require_once 'includes/db.php';

// Get the product slug from the URL
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    // Redirect to shop if no product is specified
    header("Location: shop.php");
    exit;
}

// Fetch the main product data
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, b.name AS brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.slug = ? AND p.status != 'hidden'
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

// If product doesn't exist, show a 404-style message
if (!$product) {
    include 'includes/header.php';
    echo "<div class='container' style='text-align:center; padding: 100px 0;'><h2>Product not found.</h2><a href='shop.php' class='btn btn-primary'>Back to Shop</a></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch all images for this product
$stmtImages = $pdo->prepare("SELECT image_url, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$stmtImages->execute([$product['id']]);
$images = $stmtImages->fetchAll();

// Determine stock status
$inStock = $product['stock_quantity'] > 0;

// Decode JSON specs safely
$specifications = json_decode($product['specifications'], true) ?: [];

include 'includes/header.php';
?>

<div class="container product-details-container">
    
    <!-- Left Column: Image Gallery -->
    <div class="product-gallery">
        <?php 
        $mainImg = !empty($images) ? 'assets/images/products/' . htmlspecialchars($images[0]['image_url']) : 'https://via.placeholder.com/600x600.png?text=No+Image';
        ?>
        <img src="<?php echo $mainImg; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-image" id="featured-image">
        
        <?php if (count($images) > 1): ?>
            <div class="thumbnail-grid">
                <?php foreach ($images as $img): ?>
                    <img src="assets/images/products/<?php echo htmlspecialchars($img['image_url']); ?>" 
                         alt="Thumbnail" 
                         onclick="document.getElementById('featured-image').src=this.src">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Product Info -->
    <div class="product-info">
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            <a href="shop.php">Shop</a> > 
            <a href="shop.php?category=<?php echo urlencode($product['category_name']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> > 
            <?php echo htmlspecialchars($product['brand_name']); ?>
        </p>
        
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <div class="stock-status <?php echo $inStock ? 'in-stock' : 'out-of-stock'; ?>">
            <?php echo $inStock ? '<i class="fas fa-check-circle"></i> In Stock' : '<i class="fas fa-times-circle"></i> Out of Stock'; ?>
        </div>

        <div class="price-block">
            <?php if ($product['discount_price']): ?>
                <span class="current-price">Ksh <?php echo number_format($product['discount_price']); ?></span>
                <span class="old-price">Ksh <?php echo number_format($product['price']); ?></span>
            <?php else: ?>
                <span class="current-price">Ksh <?php echo number_format($product['price']); ?></span>
            <?php endif; ?>
        </div>

        <p style="color: var(--text-muted); line-height: 1.8;">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </p>

        <!-- Add to Cart Form -->
        <form action="cart.php" method="POST" class="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="action" value="add">
            
            <input type="number" name="quantity" value="1" min="1" max="<?php echo $inStock ? $product['stock_quantity'] : 1; ?>" class="qty-input" <?php echo !$inStock ? 'disabled' : ''; ?>>
            
            <button type="submit" class="btn btn-primary" style="flex: 1;" <?php echo !$inStock ? 'disabled style="background:var(--text-muted);"' : ''; ?>>
                <i class="fas fa-cart-plus"></i> <?php echo $inStock ? 'Add to Cart' : 'Currently Unavailable'; ?>
            </button>
        </form>

        <!-- Dynamic Specifications Table -->
        <?php if (!empty($specifications)): ?>
            <h3 style="margin-top: 40px; margin-bottom: 15px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">Specifications</h3>
            <table class="specs-table">
                <tbody>
                    <?php foreach ($specifications as $key => $value): ?>
                        <tr>
                            <th><?php echo htmlspecialchars(ucfirst($key)); ?></th>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>