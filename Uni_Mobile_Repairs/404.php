<?php
// 404.php - Custom Error Page
http_response_code(404);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Upside Technicians</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-container {
            max-width: 650px;
            margin: 60px auto;
            background: #ffffff;
            padding: 45px 30px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #0284c7;
            line-height: 1;
            margin-bottom: 10px;
        }
        .error-icon {
            font-size: 3.5rem;
            color: #38bdf8;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 1.8rem;
            color: #0f172a;
            margin: 0 0 12px 0;
        }
        .error-desc {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0;">

    <?php include 'includes/header.php'; ?>

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-plug-circle-exclamation"></i>
        </div>
        <div class="error-code">404</div>
        <h2 class="error-title">Disconnected or Missing Link!</h2>
        <p class="error-desc">
            The page or component you are looking for might have been moved, renamed, or is temporarily offline. Let’s guide you back to where things work smoothly.
        </p>

        <div class="btn-group">
            <a href="index.php" class="btn-action btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="shop.php" class="btn-action btn-secondary">
                <i class="fas fa-shopping-bag"></i> Visit Shop
            </a>
            <a href="book_repair.php" class="btn-action btn-secondary">
                <i class="fas fa-tools"></i> Book Repair
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>