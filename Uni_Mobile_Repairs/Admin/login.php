<?php
// admin/login.php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($login_input) && !empty($password)) {
        try {
            // Check both username and email columns in the database
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$login_input, $login_input]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Invalid username/email or password.";
            }
        } catch (PDOException $e) {
            // Fallback query if your database table does not have an 'email' column yet
            try {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
                $stmt->execute([$login_input]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $admin['username'];
                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Invalid username or password.";
                }
            } catch (PDOException $ex) {
                $error_message = "Database Error: " . $ex->getMessage();
            }
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - UNI MOBILE REPAIRS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-container {
            max-width: 420px;
            margin: 0 auto;
            background: #1e293b;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #334155;
            border-radius: 12px;
            outline: none;
            font-size: 1rem;
            color: #f8fafc;
            background: #0f172a;
            transition: all 0.25s ease;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: #38bdf8;
            background: #0f172a;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
        }
        .login-btn {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.5);
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border-radius: 50%; font-size: 1.6rem; margin-bottom: 15px; border: 1px solid rgba(56, 189, 248, 0.3);">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2 style="color: #f8fafc; margin: 0 0 8px 0; font-size: 1.8rem;">Admin Portal</h2>
            <p style="color: #94a3b8; margin: 0; font-size: 0.95rem;">Uni Mobile Repairs Management</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 14px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 0.95rem;">
                <i class="fas fa-exclamation-circle" style="font-size: 1.1rem;"></i> 
                <div><?php echo htmlspecialchars($error_message); ?></div>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <div class="form-group">
                <label><i class="fas fa-user" style="color: #38bdf8; margin-right: 6px;"></i> Username or Email</label>
                <input type="text" name="username" class="form-control" value="" autocomplete="off" required placeholder="Enter username or email">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label><i class="fas fa-lock" style="color: #38bdf8; margin-right: 6px;"></i> Password</label>
                <input type="password" name="password" class="form-control" value="" autocomplete="new-password" required placeholder="Enter password">
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Login to Dashboard
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px;">
            <a href="../index.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; transition: color 0.2s;">
                <i class="fas fa-arrow-left"></i> Back to Main Website
            </a>
        </div>
    </div>

</body>
</html>