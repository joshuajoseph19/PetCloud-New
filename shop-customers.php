<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop_owner') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];

// Fetch Shop Details
$stmt = $pdo->prepare("SELECT * FROM shop_applications WHERE email = ? AND status = 'approved' LIMIT 1");
$stmt->execute([$user_email]);
$shop = $stmt->fetch();
$shop_id = $shop['id'];
$shopName = $shop['shop_name'];

// Fetch Unique Customers who purchased from this shop
$stmt = $pdo->prepare("SELECT u.id, u.full_name, u.email, MAX(o.phone) as phone, 
                             COUNT(DISTINCT o.id) as total_orders, 
                             MAX(o.created_at) as last_order_date
                      FROM users u
                      JOIN orders o ON u.id = o.user_id
                      JOIN order_items oi ON o.id = oi.order_id
                      JOIN products p ON oi.product_id = p.id
                      WHERE p.shop_id = ?
                      GROUP BY u.id
                      ORDER BY last_order_date DESC");
$stmt->execute([$shop_id]);
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customers - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
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

        .main-wrapper {
            margin-left: 280px;
            padding: 2.5rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (max-width: 1024px) {
            .main-wrapper {
                margin-left: 0;
                padding: 1.5rem;
            }
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            font-family: 'Outfit';
            font-size: 1.75rem;
        }

        .content-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

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
            border-bottom: 1px solid #f1f5f9;
        }

        .custom-table td {
            padding: 1.25rem 1rem;
            font-size: 0.95rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .cust-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .cust-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #eef2ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .cust-name {
            font-weight: 600;
            color: #111827;
        }

        .cust-email {
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="page-header">
            <h2>Customer Base</h2>
            <p style="color: #64748b;">View and manage your relationship with pet owners.</p>
        </div>

        <div class="content-card">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total Orders</th>
                        <th>Last Purchase</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 4rem; color: #94a3b8;">No customers yet. Sell
                                products to grow your base!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <div class="cust-info">
                                        <div class="cust-avatar">
                                            <?php echo strtoupper(substr($c['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="cust-name">
                                                <?php echo htmlspecialchars($c['full_name']); ?>
                                            </div>
                                            <div class="cust-email">
                                                <?php echo htmlspecialchars($c['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="color: #4b5563; font-weight: 500;">
                                        <?php echo htmlspecialchars($c['phone'] ?: 'N/A'); ?>
                                    </span></td>
                                <td style="font-weight: 700; color: var(--primary);">
                                    <?php echo $c['total_orders']; ?>
                                </td>
                                <td><span style="font-size: 0.85rem; color: #64748b;">
                                        <?php echo date('M d, Y', strtotime($c['last_order_date'])); ?>
                                    </span></td>
                                <td>
                                    <a href="shop-orders.php?customer_id=<?php echo $c['id']; ?>" class="btn"
                                        style="padding: 0.5rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; color: #4b5563; font-weight: 600;">View
                                        Orders</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>