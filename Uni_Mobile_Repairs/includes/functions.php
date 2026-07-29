<?php
// includes/functions.php

// Ensure session is started for CSRF protection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escapes HTML output to prevent Cross-Site Scripting (XSS)
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generates or retrieves the current CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns a hidden HTML input field containing the CSRF token
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Validates incoming POST requests against the stored CSRF token
 */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die("Security check failed: Invalid or missing CSRF token.");
        }
    }
}

/**
 * Smart, bulletproof image path resolver.
 * Works across Frontend & Admin, handles Windows/Linux slashes, and searches all upload folders.
 */
function get_product_image_url($image_path) {
    if (empty($image_path)) {
        return 'assets/images/placeholder.png';
    }

    $project_root = dirname(__DIR__); // Root directory of the project
    
    // Normalize Windows backslashes (\) to web slashes (/) and trim leading slashes
    $clean_path = ltrim(str_replace('\\', '/', $image_path), '/');
    $filename   = basename($clean_path);

    // List of candidate relative paths to check on disk
    $candidates = [
        $clean_path,
        'uploads/products/' . $filename,
        'uploads/' . $filename,
        'Admin/uploads/products/' . $filename,
        'Admin/uploads/' . $filename,
        'assets/images/' . $filename
    ];

    $found_path = null;
    foreach ($candidates as $candidate) {
        $full_disk_path = $project_root . '/' . $candidate;
        if (file_exists($full_disk_path) && is_file($full_disk_path)) {
            $found_path = $candidate;
            break;
        }
    }

    // Fallback if the image does not exist on disk
    if (!$found_path) {
        $found_path = 'assets/images/placeholder.png';
    }

    // If script is executing from inside the Admin subfolder, prepend '../'
    $script_dir = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
    if (strtolower($script_dir) === 'admin') {
        return '../' . $found_path;
    }

    return $found_path;
}

/**
 * Formats Kenyan phone numbers into standard M-Pesa format (2547XXXXXXXX)
 */
function format_phone_number($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
        return '254' . substr($phone, 1);
    } elseif (strlen($phone) === 9 && substr($phone, 0, 1) === '7') {
        return '254' . $phone;
    } elseif (strlen($phone) === 12 && substr($phone, 0, 3) === '254') {
        return $phone;
    }
    return $phone;
}

/**
 * Requests an OAuth access token from Safaricom Daraja API
 */
function get_mpesa_access_token() {
    if (!defined('MPESA_CONSUMER_KEY') || !defined('MPESA_CONSUMER_SECRET')) {
        return null;
    }
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    
    $ch = curl_init(MPESA_OAUTH_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    return $result['access_token'] ?? null;
}

/**
 * Generates a URL-friendly slug from a string (e.g. "Tecno Spark 40" -> "tecno-spark-40")
 */
function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if (function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

/**
 * Converts uploaded JPEG/PNG images to lightweight .webp format
 *
 * @param string $sourcePath File path of the temporary uploaded file
 * @param string $destinationPath Path where the webp image should be saved
 * @param int $quality Compression quality (0-100, 80 is recommended)
 * @return bool True on success, false on failure
 */
function convert_image_to_webp($sourcePath, $destinationPath, $quality = 80) {
    $mimeType = mime_content_type($sourcePath);

    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            if ($image) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            break;
        case 'image/webp':
            return move_uploaded_file($sourcePath, $destinationPath);
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    $result = imagewebp($image, $destinationPath, $quality);
    imagedestroy($image);

    return $result;
}