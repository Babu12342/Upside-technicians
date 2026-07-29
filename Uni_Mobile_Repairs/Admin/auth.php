<?php
// Admin/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the admin is logged in via session flags or database role
$is_admin = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) || 
            (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if (!$is_admin) {
    header("Location: login.php");
    exit;
}
?>