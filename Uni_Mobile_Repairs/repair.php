<?php
// shop.php
require_once 'includes/header.php';

// Fetch categories for filter pills
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

// Get selected category or search term
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build dynamic product query
$query = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($selectedCategory > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $selectedCategory;
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="container page-content">
    
    <!-- Category Filter Pills -->
    <div class="shop-header">
        <h1 class="page-title">Shop Electronics & Accessories</h1>
        
        <div class="category-pills">
            <a href="shop.php" class="<?php echo ($selectedCategory === 0) ? 'active' : ''; ?>">
                <i class="fa-solid fa-border-all"></i> All Products
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="shop.php?category=<?php echo $cat['id']; ?>" 
                   class="<?php echo ($selectedCategory === (int)$cat['id']) ? 'active' : ''; ?>">
                    <?php echo e($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Product Grid or Empty State -->
    <?php if (count($products) > 0): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <?php if (!empty($product['image'])): ?>
                            <img src="uploads/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                        <?php else: ?>
                            <div class="no-img"><i class="fa-solid fa-mobile-screen-button"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <span class="product-category"><?php echo e($product['category_name'] ?? 'General'); ?></span>
                        <h3 class="product-title"><?php echo e($product['name']); ?></h3>
                        <p class="product-price">Ksh <?php echo number_format($product['price'], 2); ?></p>
                        
                        <form action="cart.php" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn-add">
                                <i class="fa-solid fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Styled Empty State -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h2>No Products Found</h2>
            <p>We couldn't find any items matching your selected criteria.</p>
            <a href="shop.php" class="btn btn-primary">
                <i class="fa-solid fa-rotate-left"></i> Reset Filters
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>