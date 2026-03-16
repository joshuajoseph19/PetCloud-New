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

// Handle Status Update
if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $newStatus = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$newStatus, $oid])) {
        // Log notification for user? (Optional enhancement)
        header("Location: shop-orders.php?success=Status Updated");
        exit();
    }
}

// Fetch Orders containing this shop's products
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email as user_email, o.phone, 
                             SUM(oi.price_at_purchase * oi.quantity) as total_value,
                             GROUP_CONCAT(p.name SEPARATOR ', ') as product_summary
                      FROM orders o
                      JOIN users u ON o.user_id = u.id
                      JOIN order_items oi ON o.id = oi.order_id
                      JOIN products p ON oi.product_id = p.id
                      WHERE p.shop_id = ? AND o.status != 'Cancelled'
                      GROUP BY o.id
                      ORDER BY o.created_at DESC");
$stmt->execute([$shop_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Management - PetCloud</title>
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

        .order-card {
            background: white;
            border-radius: 1.25rem;
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: 0.2s;
        }

        .order-card:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 1rem;
        }

        .order-id {
            font-family: 'Outfit';
            font-weight: 700;
            font-size: 1.1rem;
            color: #111827;
        }

        .order-date {
            font-size: 0.8rem;
            color: #64748b;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1fr;
            gap: 2rem;
        }

        .label {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: block;
        }

        .value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-Pending {
            background: #fff7ed;
            color: #c2410c;
        }

        .status-Accepted {
            background: #eef2ff;
            color: #4f46e5;
        }

        .status-Shipped {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .status-Delivered {
            background: #ecfdf5;
            color: #047857;
        }

        .status-Cancelled {
            background: #fef2f2;
            color: #ef4444;
        }

        .status-select {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            font-size: 0.85rem;
            outline: none;
        }

        .btn-update {
            background: #111827;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="page-header">
            <h2>Orders Management</h2>
            <p style="color: #64748b;">Review customer orders and update shipping status.</p>
        </div>

        <?php if (empty($orders)): ?>
            <div
                style="background: white; border-radius: 1.5rem; padding: 5rem; text-align: center; border: 1px dashed #cbd5e1;">
                <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                <p style="color: #64748b; font-weight: 500;">No orders found for your shop yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['id']; ?>
                                <?php if ($order['payment_id']): ?>
                                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 400; margin-left: 0.5rem;">(Txn:
                                        <?php echo $order['payment_id']; ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="order-date">Placed on
                                <?php echo date('M d, Y • h:i A', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </div>

                    <div class="content-grid">
                        <div>
                            <span class="label">Customer Details</span>
                            <div class="value">
                                <?php echo htmlspecialchars($order['full_name']); ?>
                            </div>
                            <div style="font-size: 0.85rem; color: #64748b;">
                                <?php echo htmlspecialchars($order['user_email']); ?> |
                                <?php echo htmlspecialchars($order['phone']); ?>
                            </div>
                        </div>
                        <div>
                            <span class="label">Items Breakdown</span>
                            <div class="value" style="font-size: 0.85rem; color: #475569;">
                                <?php echo htmlspecialchars($order['product_summary']); ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span class="label">Order Total</span>
                            <div style="font-size: 1.25rem; font-weight: 800; font-family:'Outfit'; color: #10b981;">₹
                                <?php echo number_format($order['total_value'], 2); ?>
                            </div>
                        </div>
                    </div>

                    <form method="POST"
                        style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f8fafc; display: flex; align-items: center; justify-content: flex-end; gap: 1rem;">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Update Status:</label>
                        <select name="status" class="status-select">
                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="Accepted" <?php echo $order['status'] == 'Accepted' ? 'selected' : ''; ?>>Accepted
                            </option>
                            <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped
                            </option>
                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered
                            </option>
                            <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled
                            </option>
                        </select>
                        <button type="submit" name="update_status" class="btn-update">Save Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</body>

</html>