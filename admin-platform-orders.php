<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

// --- Fetch Platform Settings for Commission ---
$settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key = 'commission_rate'")->fetch();
$commissionRate = ($settings && isset($settings['setting_value'])) ? floatval($settings['setting_value']) / 100 : 0.10;

// --- Ensure Orders table exists for demo ---
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Processing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$checkOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
if ($checkOrders == 0) {
    $pdo->exec("INSERT INTO orders (user_id, total_amount, status) VALUES 
        (1, 1500.00, 'Completed'),
        (2, 450.00, 'Completed'),
        (3, 89.99, 'Pending'),
        (1, 210.00, 'Completed')");
}

// --- Fetch Orders with Customer details ---
$sql = "SELECT o.*, u.full_name as customer_name FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll();

$totalPlatformRevenue = 0;
foreach ($orders as $o) {
    if ($o['status'] == 'Completed') {
        $totalPlatformRevenue += ($o['total_amount'] * $commissionRate);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Platform Orders & Revenue - Admin Panel</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --sidebar: #111827;
            --bg: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
        }

        .main-layout {
            margin-left: 260px;
            padding: 2.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Outfit';
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
        }

        .revenue-banner {
            background: var(--sidebar);
            color: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .revenue-stat h3 {
            opacity: 0.8;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .revenue-stat .val {
            font-size: 3rem;
            font-weight: 800;
            font-family: 'Outfit';
        }

        .admin-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            background: #f9fafb;
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
        }

        .data-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        .tag {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-Completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-Pending {
            background: #fef3c7;
            color: #b45309;
        }

        .commission-cell {
            color: var(--primary);
            font-weight: 700;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <div class="page-header">
            <h2 class="page-title">Platform & Revenue Tracking</h2>
        </div>

        <div class="revenue-banner">
            <div class="revenue-stat">
                <h3>Total Platform Commission (10%)</h3>
                <div class="val">₹
                    <?php echo number_format($totalPlatformRevenue, 2); ?>
                </div>
            </div>
            <div style="text-align: right;">
                <button
                    style="background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer;">
                    <i class="fa-solid fa-file-export"></i> Export Financial Report
                </button>
            </div>
        </div>

        <div class="admin-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Platform Fee (10%)</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Payout</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><strong>#PET-
                                    <?php echo $o['id']; ?>
                                </strong></td>
                            <td>
                                <?php echo htmlspecialchars($o['customer_name']); ?>
                            </td>
                            <td style="font-weight: 600;">₹
                                <?php echo number_format($o['total_amount'], 2); ?>
                            </td>
                            <td class="commission-cell">+₹
                                <?php echo number_format($o['total_amount'] * $commissionRate, 2); ?>
                            </td>
                            <td style="color: #64748b;">
                                <?php echo date('M d, Y', strtotime($o['created_at'])); ?>
                            </td>
                            <td><span class="tag status-<?php echo $o['status']; ?>">
                                    <?php echo $o['status']; ?>
                                </span></td>
                            <td>
                                <?php if ($o['status'] == 'Completed'): ?>
                                    <span style="color: #10b981; font-weight: 700;"><i class="fa-solid fa-circle-check"></i>
                                        Disbursed</span>
                                <?php else: ?>
                                    <span style="color: #94a3b8;"><i class="fa-solid fa-clock"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach;
                    if (empty($orders))
                        echo "<tr><td colspan='7' style='text-align:center; padding:3rem; color:#9ca3af;'>No orders recorded on the platform yet.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>