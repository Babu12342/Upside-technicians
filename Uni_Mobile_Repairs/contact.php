<?php
// contact.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($phone) && !empty($message)) {
        // --- TELEGRAM NOTIFICATION ---
        $telegram_message  = "✉️ *New Contact Message - Uni Mobile* ✉️\n\n";
        $telegram_message .= "*Name:* " . $name . "\n";
        $telegram_message .= "*Phone:* " . $phone . "\n";
        $telegram_message .= "*Message:* " . $message . "\n";

        $botToken = "8874653683:AAE4KpvDuTvHGabZTe5oIo9lW5oKpviqbIs"; 
        $chatId = "5232258264";
        
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
        
        $success_message = "Your message has been sent successfully! We will get back to you soon.";
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
    <title>Contact Us - Upside Technicians</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .contact-container {
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
        .contact-info {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .info-card {
            flex: 1;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #e2e8f0;
            min-width: 200px;
        }
        .info-card i {
            font-size: 1.8rem;
            color: #0284c7;
            margin-bottom: 10px;
        }
        .info-card h4 {
            margin: 0 0 5px 0;
            color: #0f172a;
        }
        .info-card p {
            margin: 0;
            color: #64748b;
            font-size: 0.95rem;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0;">

    <?php include 'includes/header.php'; ?>

    <div class="contact-container">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: #e0f2fe; color: #0284c7; border-radius: 50%; font-size: 1.5rem; margin-bottom: 15px;">
                <i class="fas fa-envelope"></i>
            </div>
            <h2 style="color: #0f172a; margin: 0 0 8px 0; font-size: 2rem;">Get in Touch</h2>
            <p style="color: #64748b; margin: 0; font-size: 1rem;">Have a question or need assistance? Drop us a message.</p>
        </div>

        <div class="contact-info">
            <div class="info-card">
                <i class="fas fa-map-marker-alt"></i>
                <h4>Location</h4>
                <p>Eldoret, Kenya</p>
            </div>
            <div class="info-card">
                <i class="fas fa-phone-alt"></i>
                <h4>Phone</h4>
                <p>0712345678</p> <!-- You can update this to your actual business number -->
            </div>
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

        <form action="" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label><i class="fas fa-user" style="color: #0284c7; margin-right: 6px;"></i> Your Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone" style="color: #0284c7; margin-right: 6px;"></i> Phone Number *</label>
                    <input type="text" name="phone" class="form-control" required placeholder="e.g., 0712345678">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-comment-dots" style="color: #0284c7; margin-right: 6px;"></i> Message *</label>
                <textarea name="message" rows="5" class="form-control" required placeholder="How can we help you?"></textarea>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send Message
            </button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>