<?php
session_start();
require_once 'db_connect.php';

// Check if logged in and is shop owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop_owner') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];

// --- Time-based Greeting Logic (IST) ---
date_default_timezone_set('Asia/Kolkata');
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good Afternoon";
} elseif ($hour >= 17 && $hour < 21) {
    $greeting = "Good Evening";
} else {
    $greeting = "Good Night";
}

// Fetch Shop Details
$stmt = $pdo->prepare("SELECT * FROM shop_applications WHERE email = ? AND status = 'approved' LIMIT 1");
$stmt->execute([$user_email]);
$shop = $stmt->fetch();

if (!$shop) {
    echo "Your shop application is pending or was not found. Please contact admin.";
    exit();
}

$shop_id = $shop['id'];
$shopName = $shop['shop_name'];
$shopCategory = $shop['shop_category'];

// Fetch Statistics
// 1. Total Revenue
$stmtRev = $pdo->prepare("SELECT SUM(oi.price_at_purchase * oi.quantity) 
                         FROM order_items oi 
                         JOIN products p ON oi.product_id = p.id 
                         JOIN orders o ON oi.order_id = o.id
                         WHERE p.shop_id = ? AND o.status != 'Cancelled'");
$stmtRev->execute([$shop_id]);
$totalRevenue = $stmtRev->fetchColumn() ?: 0;

// 2. Today's Orders
$stmtToday = $pdo->prepare("SELECT COUNT(DISTINCT oi.order_id) 
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           JOIN orders o ON oi.order_id = o.id
                           WHERE p.shop_id = ? AND DATE(o.created_at) = CURDATE()");
$stmtToday->execute([$shop_id]);
$todayOrders = $stmtToday->fetchColumn();

// 3. Total Products
$stmtProd = $pdo->prepare("SELECT COUNT(*) FROM products WHERE shop_id = ?");
$stmtProd->execute([$shop_id]);
$totalProducts = $stmtProd->fetchColumn();

// 4. Pending Shipments
$stmtPending = $pdo->prepare("SELECT COUNT(DISTINCT o.id) 
                             FROM orders o 
                             JOIN order_items oi ON o.id = oi.order_id 
                             JOIN products p ON oi.product_id = p.id 
                             WHERE p.shop_id = ? AND o.status = 'Pending'");
$stmtPending->execute([$shop_id]);
$pendingShipments = $stmtPending->fetchColumn();

// Fetch Recent Orders
$stmtRecent = $pdo->prepare("SELECT o.*, u.full_name as customer_name, SUM(oi.price_at_purchase * oi.quantity) as order_total
                            FROM orders o
                            JOIN users u ON o.user_id = u.id
                            JOIN order_items oi ON o.id = oi.order_id
                            JOIN products p ON oi.product_id = p.id
                            WHERE p.shop_id = ?
                            GROUP BY o.id
                            ORDER BY o.created_at DESC
                            LIMIT 5");
$stmtRecent->execute([$shop_id]);
$recentOrders = $stmtRecent->fetchAll();

// Fetch Top Selling Products
$stmtTopP = $pdo->prepare("SELECT p.name, p.image_url, COUNT(oi.id) as sales_count, SUM(oi.quantity) as total_qty
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          WHERE p.shop_id = ?
                          GROUP BY p.id
                          ORDER BY sales_count DESC
                          LIMIT 4");
$stmtTopP->execute([$shop_id]);
$topProducts = $stmtTopP->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Manager - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --bg: #f8fafc;
            --sidebar: #ffffff;
            --text-main: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-main);
        }

        .main-wrapper {
            margin-left: 280px;
            padding: 2.5rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .welcome-section {
            margin-bottom: 2.5rem;
        }

        .welcome-section h2 {
            font-family: 'Outfit';
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: #64748b;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: #fff;
            padding: 1.75rem;
            border-radius: 1.25rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            font-family: 'Outfit';
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1.1fr;
            gap: 2rem;
        }

        .content-card {
            background: #fff;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .card-title {
            font-family: 'Outfit';
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* Table Style */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            text-align: left;
            padding: 1rem;
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #f1f5f9;
        }

        .custom-table td {
            padding: 1.25rem 1rem;
            font-size: 0.95rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-pill {
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff7ed;
            color: #c2410c;
        }

        .status-shipped {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .status-delivered {
            background: #ecfdf5;
            color: #047857;
        }

        /* Top Product Card */
        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .product-img {
            width: 56px;
            height: 56px;
            border-radius: 0.75rem;
            object-fit: cover;
        }

        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .main-wrapper {
                margin-left: 0;
                padding: 1.5rem;
            }

            .dashboard-content {
                grid-template-columns: 1fr;
            }

            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }

            .content-card {
                padding: 1.25rem;
            }

            .custom-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <section class="welcome-section">
            <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>! 👋</h2>
            <p>Welcome back to your shop control center. Here's what's happening today.</p>
        </section>

        <!-- Stats Overview -->
        <div class="stat-grid">
            <a href="shop-reports.php" class="stat-card">
                <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;"><i
                        class="fa-solid fa-indian-rupee-sign"></i></div>
                <div class="stat-value">₹<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </a>
            <a href="shop-orders.php?filter=today" class="stat-card">
                <div class="stat-icon" style="background: #ecfdf5; color: #10b981;"><i
                        class="fa-solid fa-bag-shopping"></i></div>
                <div class="stat-value"><?php echo $todayOrders; ?></div>
                <div class="stat-label">Today's Orders</div>
            </a>
            <a href="shop-products.php" class="stat-card">
                <div class="stat-icon" style="background: #fefce8; color: #eab308;"><i
                        class="fa-solid fa-boxes-stacked"></i></div>
                <div class="stat-value"><?php echo $totalProducts; ?></div>
                <div class="stat-label">Total Products</div>
            </a>
            <a href="shop-orders.php?status=Pending" class="stat-card">
                <div class="stat-icon" style="background: #fef2f2; color: #ef4444;"><i
                        class="fa-solid fa-truck-ramp-box"></i></div>
                <div class="stat-value"><?php echo $pendingShipments; ?></div>
                <div class="stat-label">Pending Shipments</div>
            </a>
        </div>

        <div class="dashboard-content">
            <!-- Recent Orders -->
            <div class="content-card">
                <div class="card-header">
                    <div class="card-title">Recent Orders</div>
                    <a href="shop-orders.php"
                        style="color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 600;">View
                        All</a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; color:#94a3b8; padding: 3rem;">No orders yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td style="font-weight:700;">#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td style="font-weight:600;">₹<?php echo number_format($order['order_total'], 2); ?></td>
                                    <td>
                                        <span class="status-pill status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td><a href="shop-orders.php?id=<?php echo $order['id']; ?>" class="icon-btn"
                                            style="color: #64748b;"><i class="fa-solid fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Selling -->
            <div class="content-card">
                <div class="card-header">
                    <div class="card-title">Top Selling</div>
                    <i class="fa-solid fa-fire" style="color: #f97316;"></i>
                </div>
                <div class="product-list">
                    <?php if (empty($topProducts)): ?>
                        <p style="text-align:center; color:#94a3b8; padding: 2rem;">Sell items to see tops!</p>
                    <?php else: ?>
                        <?php foreach ($topProducts as $tp): ?>
                            <div class="product-item">
                                <img src="<?php echo $tp['image_url']; ?>" class="product-img">
                                <div style="flex:1;">
                                    <div style="font-weight:600; font-size:0.95rem;">
                                        <?php echo htmlspecialchars($tp['name']); ?></div>
                                    <div style="font-size:0.8rem; color:#64748b;"><?php echo $tp['sales_count']; ?> conversions
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-weight:700; color: #10b981;"><?php echo $tp['total_qty']; ?></div>
                                    <div style="font-size:0.75rem; color:#94a3b8;">Sold</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="shop-products.php" class="stat-card"
                    style="display:block; text-align:center; padding: 1rem; margin-top: 1rem; color: var(--primary); font-weight: 600; font-size:0.9rem;">
                    Manage Inventory
                </a>
            </div>
        </div>
    </main>

    <script>
        const shopSearch = document.getElementById('shop-search');
        if (shopSearch) {
            shopSearch.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    const query = this.value.toLowerCase().trim();
                    if (!query) return;

                    if (query.includes('order')) {
                        window.location.href = 'shop-orders.php';
                    } else if (query.includes('product') || query.includes('item')) {
                        window.location.href = 'shop-products.php';
                    } else {
                        window.location.href = 'shop-orders.php?search=' + encodeURIComponent(query);
                    }
                }
            });

            // Live filtering for the recent orders table
            shopSearch.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const rows = document.querySelectorAll('.custom-table tbody tr');

                rows.forEach(row => {
                    if (row.cells.length < 2) return; // Skip "No orders yet" message
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</body>

</html>