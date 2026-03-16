<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.html');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

// --- Fetch Statistics ---
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalClients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$pendingApps = $pdo->query("SELECT COUNT(*) FROM shop_applications WHERE status = 'pending'")->fetchColumn();
$pendingAdoptions = $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'pending'")->fetchColumn();
$adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

// --- Fetch Recent Platform Data ---
$recentOrders = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700&display=swap"
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
            color: #1e293b;
        }

        .main-layout {
            margin-left: 260px;
            padding: 2.5rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .page-title {
            font-family: 'Outfit';
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #111827;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-link {
            text-decoration: none;
            display: block;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 1.25rem;
            border: 1px solid #e5e7eb;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            position: absolute;
            right: -10px;
            top: -10px;
            font-size: 5rem;
            opacity: 0.03;
            color: var(--sidebar);
            transform: rotate(-15deg);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .admin-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            padding: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        .status-pill {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-Pending {
            background: #fef3c7;
            color: #b45309;
        }

        .status-Completed {
            background: #d1fae5;
            color: #065f46;
        }

        .user-list-mini {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .user-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 1rem;
            background: #f8fafc;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .user-info h4 {
            font-size: 0.875rem;
            font-weight: 700;
        }

        .user-info p {
            font-size: 0.75rem;
            color: #64748b;
        }

        .btn-view-all {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .main-layout {
                margin-left: 0;
                padding: 1.5rem;
            }

            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.5rem;
            }

            .admin-card {
                padding: 1.5rem;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .data-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 class="page-title" style="margin-bottom: 0;">Platform Performance Overview</h2>

            <!-- System Status Indicator (New) -->
            <div style="display: flex; gap: 1rem;">
                <?php
                require_once 'config.php';

                // 1. Database Check
                $dbOnline = false;
                try {
                    $pdo->query("SELECT 1");
                    $dbOnline = true;
                } catch (Exception $e) {
                }

                // 2. Gateway Check
                $gatewayLive = (defined('PAYMENT_MODE') && PAYMENT_MODE === 'live');
                $keyConfigured = (defined('RAZORPAY_KEY_ID') && strpos(RAZORPAY_KEY_ID, 'YOUR_') === false);
                ?>

                <div
                    style="background: white; padding: 0.5rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; font-weight: 600;">
                    <span
                        style="height: 8px; width: 8px; border-radius: 50%; background: <?php echo $dbOnline ? '#10b981' : '#ef4444'; ?>; box-shadow: 0 0 6px <?php echo $dbOnline ? '#10b981' : '#ef4444'; ?>;"></span>
                    <span>Database: <?php echo $dbOnline ? 'Online' : 'Offline'; ?></span>
                </div>

                <div
                    style="background: white; padding: 0.5rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; font-weight: 600;">
                    <span
                        style="height: 8px; width: 8px; border-radius: 50%; background: <?php echo $keyConfigured ? '#10b981' : '#f59e0b'; ?>;"></span>
                    <span>Gateway: <?php echo $gatewayLive ? 'Live' : 'Test Mode'; ?></span>
                </div>

                <div
                    style="background: white; padding: 0.5rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; font-weight: 600;">
                    <i class="fa-solid fa-server" style="color: #64748b;"></i>
                    <span>Env: <?php echo $gatewayLive ? 'Production' : 'Development'; ?></span>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <a href="admin-users.php" class="stat-link">
                <div class="stat-card">
                    <i class="fa-solid fa-users stat-icon"></i>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Platform Users</div>
                </div>
            </a>
            <a href="admin-users.php?role=client" class="stat-link">
                <div class="stat-card">
                    <i class="fa-solid fa-user-tag stat-icon"></i>
                    <div class="stat-value"><?php echo $totalClients; ?></div>
                    <div class="stat-label">Registered Clients</div>
                </div>
            </a>
            <a href="admin-shop-approvals.php" class="stat-link">
                <div class="stat-card">
                    <i class="fa-solid fa-store stat-icon"></i>
                    <div class="stat-value" style="color: #f59e0b;"><?php echo $pendingApps; ?></div>
                    <div class="stat-label">Pending Shops</div>
                </div>
            </a>
            <a href="admin-adoptions.php" class="stat-link">
                <div class="stat-card">
                    <i class="fa-solid fa-heart stat-icon"></i>
                    <div class="stat-value" style="color: #ef4444;"><?php echo $pendingAdoptions; ?></div>
                    <div class="stat-label">New Adoptions</div>
                </div>
            </a>
        </div>

        <div class="dashboard-content">
            <!-- Recent platform activity -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Transactions</h3>
                    <a href="admin-platform-orders.php" class="btn-view-all">View All Orders</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong>#ORD-<?php echo $order['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                <td style="color: #10b981; font-weight: 700;">
                                    ₹<?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                <td style="color: #64748b;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                </td>
                                <td><span
                                        class="status-pill status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
                                </td>
                            </tr>
                        <?php endforeach;
                        if (empty($recentOrders))
                            echo "<tr><td colspan='5' style='text-align:center; padding:2rem; color:#94a3b8;'>No recent orders found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>

            <!-- New User Registration -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title">New Users</h3>
                    <a href="admin-users.php" class="btn-view-all">Manage Users</a>
                </div>
                <div class="user-list-mini">
                    <?php foreach ($recentUsers as $ru): ?>
                        <div class="user-item">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($ru['full_name']); ?>&background=random"
                                class="user-avatar" alt="Avatar">
                            <div class="user-info">
                                <h4><?php echo htmlspecialchars($ru['full_name']); ?></h4>
                                <p><?php echo htmlspecialchars($ru['role']); ?> • Joined
                                    <?php echo date('M d', strtotime($ru['created_at'])); ?>
                                </p>
                            </div>
                            <?php if ($ru['role'] == 'admin'): ?>
                                <i class="fa-solid fa-shield-check" style="margin-left: auto; color: #10b981;"></i>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </main>

</body>

</html>