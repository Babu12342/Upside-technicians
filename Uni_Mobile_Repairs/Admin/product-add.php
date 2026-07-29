<?php
require_once __DIR__ . '/auth.php';
// Admin/product-add.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Fetch active categories
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error = "Failed to load categories: " . $e->getMessage();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 10);
    $description = trim($_POST['description'] ?? '');
    $image_name = null;

    // Validate input fields
    if (empty($name)) {
        $error = 'Product name is required.';
    } elseif ($category_id <= 0) {
        $error = 'Please select a valid category.';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than Ksh 0.';
    } else {
        // Handle File Upload securely
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fileError = $_FILES['image']['error'];

            if ($fileError === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $allowedMimeTypes  = ['image/jpeg', 'image/png', 'image/webp'];

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);

                if (in_array($fileExtension, $allowedExtensions) && in_array($mimeType, $allowedMimeTypes)) {
                    $newFileName = md5(time() . $fileName . rand(1000, 9999)) . '.' . $fileExtension;
                    $uploadDir = __DIR__ . '/../uploads/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $image_name = $newFileName;
                    } else {
                        $error = 'Failed to move uploaded image to storage directory.';
                    }
                } else {
                    $error = 'Invalid file format. Only JPG, PNG, and WEBP image files are allowed.';
                }
            } else {
                $error = 'Image upload error code: ' . $fileError;
            }
        }

        // Save to Database
        if (empty($error)) {
            try {
                $slug = generate_slug($name);
                
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $name, $slug, $description, $price, $stock, $image_name]);

                $_SESSION['flash_success'] = "Product '{$name}' added successfully!";
                header('Location: product-add.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Database Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Admin Panel</title>
    <link rel="stylesheet" href="../Assets/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 40px 20px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
        }
        .form-card {
            max-width: 580px;
            margin: 0 auto;
            background: #1e293b;
            padding: 36px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        .form-control-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #334155;
            border-radius: 10px;
            box-sizing: border-box;
            background: #0f172a;
            color: #f8fafc;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control-input:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.5);
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
        }
    </style>
</head>
<body>

<div class="form-card">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; color: #f8fafc; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-plus-circle" style="color: #38bdf8;"></i> Add Product
        </h2>
        <a href="index.php" style="color: #38bdf8; text-decoration: none; font-size: 0.9rem; font-weight: 600;">← Back</a>
    </div>

    <?php if (!empty($message)): ?>
        <div style="background-color: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 14px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check"></i> <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 14px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 20px;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo e($error); ?>
        </div>
    <?php endif; ?>

    <form action="product-add.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        
        <div style="margin-bottom: 18px;">
            <label class="form-label">Product Name *</label>
            <input type="text" name="name" required class="form-control-input">
        </div>

        <div style="margin-bottom: 18px;">
            <label class="form-label">Category *</label>
            <select name="category_id" required class="form-control-input">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
            <div>
                <label class="form-label">Price (Ksh) *</label>
                <input type="number" step="0.01" min="0.01" name="price" required class="form-control-input">
            </div>
            <div>
                <label class="form-label">Stock Units</label>
                <input type="number" min="0" name="stock" value="10" class="form-control-input">
            </div>
        </div>

        <div style="margin-bottom: 18px;">
            <label class="form-label">Product Image</label>
            <input type="file" name="image" accept="image/*" class="form-control-input" style="padding: 9px;">
        </div>

        <div style="margin-bottom: 24px;">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control-input" style="resize: vertical;"></textarea>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-cloud-arrow-up"></i> Save Product
        </button>

    </form>
</div>

</body>
</html>