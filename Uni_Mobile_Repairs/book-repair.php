<?php
// book_repair.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $device_model = trim($_POST['device_model'] ?? '');
    $issue_description = trim($_POST['issue_description'] ?? '');
    $service_date = trim($_POST['service_date'] ?? '');
    
    // Handle image upload with WebP conversion
    $image_name = '';
    if (isset($_FILES['device_image']) && $_FILES['device_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['device_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['device_image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Set destination filename with .webp extension
            $image_name = uniqid('repair_', true) . '.webp';
            $target_path = $upload_dir . $image_name;

            // Attempt WebP compression
            if (!convert_image_to_webp($file_tmp, $target_path, 80)) {
                // Fallback to original file format if WebP conversion is unsupported
                $image_name = uniqid('repair_', true) . '.' . $file_ext;
                move_uploaded_file($file_tmp, $upload_dir . $image_name);
            }
        }
    }

    if (!empty($customer_name) && !empty($phone) && !empty($device_model) && !empty($issue_description)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO repairs (customer_name, phone, device_model, issue_description, service_date, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");
            $stmt->execute([$customer_name, $phone, $device_model, $issue_description, $service_date, $image_name]);
            
            // --- TELEGRAM NOTIFICATION ---
            $telegram_message  = "🛠️ *New Repair Booking - Upside Technicians* 🛠️\n\n";
            $telegram_message .= "*Name:* " . $customer_name . "\n";
            $telegram_message .= "*Phone:* " . $phone . "\n";
            $telegram_message .= "*Device:* " . $device_model . "\n";
            $telegram_message .= "*Issue:* " . $issue_description . "\n";
            $telegram_message .= "*Date:* " . (!empty($service_date) ? $service_date : 'Not specified') . "\n";

            // Safely fetch Telegram credentials from environment variables
            $botToken = getenv('TELEGRAM_BOT_TOKEN') ?: "8874653683:AAE4KpvDuTvHGabZTe5oIo9lW5oKpviqbIs"; 
            $chatId   = getenv('TELEGRAM_CHAT_ID') ?: "5232258264";
            
            $website = "https://api.telegram.org/bot" . $botToken;
            $params = [
                'chat_id' => $chatId,
                'text' => $telegram_message,
                'parse_mode' => 'Markdown'
            ];
            
            $ch = curl_init($website . '/sendMessage');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
            // -----------------------------

            $success_message = "Repair booked successfully! Our technician will reach out to you shortly.";
        } catch (PDOException $e) {
            $error_message = "Database Error: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Repair - Upside Technicians</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .repair-container {
            max-width: 750px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            outline: none;
            font-size: 1rem;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: #0284c7;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1);
        }
        .file-upload-box {
            border: 2px dashed #cbd5e1;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .file-upload-box:hover {
            border-color: #0284c7;
        }
        .submit-btn {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }
        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0;">

    <?php include 'includes/header.php'; ?>

    <div class="repair-container">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: #e0f2fe; color: #0284c7; border-radius: 50%; font-size: 1.5rem; margin-bottom: 15px;">
                <i class="fas fa-tools"></i>
            </div>
            <h2 style="color: #0f172a; margin: 0 0 8px 0; font-size: 2rem;">Book a Device Repair</h2>
            <p style="color: #64748b; margin: 0; font-size: 1rem;">Expert diagnostics and fast repair services. Tell us about your device.</p>
        </div>

        <?php if (!empty($success_message)): ?>
            <div style="background: #dcfce7; color: #166534; padding: 16px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 12px; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> 
                <div><?php echo $success_message; ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 12px; border: 1px solid #fecaca;">
                <i class="fas fa-exclamation-circle" style="font-size: 1.2rem;"></i> 
                <div><?php echo $error_message; ?></div>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label><i class="fas fa-user" style="color: #0284c7; margin-right: 6px;"></i> Your Name *</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone" style="color: #0284c7; margin-right: 6px;"></i> Phone Number *</label>
                    <input type="text" name="phone" class="form-control" required placeholder="e.g., 0712345678">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label><i class="fas fa-mobile-alt" style="color: #0284c7; margin-right: 6px;"></i> Device Brand & Model *</label>
                    <input type="text" name="device_model" class="form-control" required placeholder="e.g., Tecno Spark 40 Pro">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt" style="color: #0284c7; margin-right: 6px;"></i> Preferred Service Date</label>
                    <input type="date" name="service_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-clipboard-list" style="color: #0284c7; margin-right: 6px;"></i> Issue Description *</label>
                <textarea name="issue_description" rows="4" class="form-control" required placeholder="Describe the problem (e.g., cracked screen, battery drains fast, charging port broken)..."></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-camera" style="color: #0284c7; margin-right: 6px;"></i> Device Photo (Optional)</label>
                <div class="file-upload-box">
                    <input type="file" name="device_image" accept="image/*" style="width: 100%;">
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Submit Repair Booking
            </button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>