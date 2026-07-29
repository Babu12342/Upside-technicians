<?php
// admin/repairs.php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle status update if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $repair_id = intval($_POST['repair_id']);
    $new_status = trim($_POST['status']);
    
    try {
        $update_stmt = $pdo->prepare("UPDATE repairs SET status = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $repair_id]);
        header("Location: repairs.php?success=1");
        exit();
    } catch (PDOException $e) {
        $db_error = $e->getMessage();
    }
}

// Fetch all repairs from the database
try {
    $stmt = $pdo->query("SELECT * FROM repairs ORDER BY created_at DESC");
    $repairs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $repairs = [];
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Repairs - Admin | Upside Technicians</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #0f172a; font-family: 'Inter', sans-serif; margin: 0; padding: 0; color: #f8fafc;">

    <div style="display: flex; min-height: 100vh;">
        <div style="flex: 1; padding: 40px; max-width: 1280px; margin: 0 auto;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 style="color: #f8fafc; font-size: 1.8rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-tools" style="color: #38bdf8;"></i> Manage Repair Bookings
                </h1>
                <a href="index.php" style="background: #334155; color: #fff; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: background 0.2s;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 14px; border-radius: 10px; margin-bottom: 24px; font-weight: 600;">
                    Repair status updated successfully!
                </div>
            <?php endif; ?>

            <?php if (isset($db_error)): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 14px; border-radius: 10px; margin-bottom: 24px;">
                    Database Error: <?php echo htmlspecialchars($db_error); ?>
                </div>
            <?php endif; ?>

            <div style="background: #1e293b; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #0f172a; color: #38bdf8; border-bottom: 2px solid #334155;">
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">ID</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Customer</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Phone</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Device Model</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Issue</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Service Date</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Image</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Status</th>
                            <th style="padding: 16px; font-size: 0.8rem; text-transform: uppercase;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($repairs)): ?>
                            <?php foreach ($repairs as $repair): ?>
                                <tr style="border-bottom: 1px solid #334155;">
                                    <td style="padding: 16px; color: #38bdf8; font-weight: 700;">#<?php echo $repair['id']; ?></td>
                                    <td style="padding: 16px; font-weight: 600; color: #f8fafc;"><?php echo htmlspecialchars($repair['customer_name']); ?></td>
                                    <td style="padding: 16px; color: #cbd5e1;"><?php echo htmlspecialchars($repair['phone']); ?></td>
                                    <td style="padding: 16px; color: #cbd5e1; font-weight: 600;"><?php echo htmlspecialchars($repair['device_model']); ?></td>
                                    <td style="padding: 16px; color: #94a3b8; max-width: 200px;"><?php echo htmlspecialchars($repair['issue_description']); ?></td>
                                    <td style="padding: 16px; color: #94a3b8;"><?php echo htmlspecialchars($repair['service_date'] ?: 'N/A'); ?></td>
                                    <td style="padding: 16px;">
                                        <?php if (!empty($repair['image'])): ?>
                                            <a href="../uploads/<?php echo htmlspecialchars($repair['image']); ?>" target="_blank" style="color: #38bdf8; text-decoration: none; font-weight: 600;"><i class="fas fa-image"></i> View</a>
                                        <?php else: ?>
                                            <span style="color: #64748b;">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 16px;">
                                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; 
                                            <?php 
                                                if ($repair['status'] === 'Completed') echo 'background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.4);';
                                                elseif ($repair['status'] === 'In Progress') echo 'background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.4);';
                                                else echo 'background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4);';
                                            ?>">
                                            <?php echo htmlspecialchars($repair['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px;">
                                        <form action="" method="POST" style="display: flex; gap: 6px; align-items: center;">
                                            <input type="hidden" name="repair_id" value="<?php echo $repair['id']; ?>">
                                            <select name="status" style="padding: 8px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: #f8fafc; font-size: 0.85rem; outline: none;">
                                                <option value="Pending" <?php if($repair['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="In Progress" <?php if($repair['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                                                <option value="Completed" <?php if($repair['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                            </select>
                                            <button type="submit" name="update_status" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.85rem;"><i class="fas fa-save"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="padding: 30px; text-align: center; color: #94a3b8;">No repair bookings found yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>