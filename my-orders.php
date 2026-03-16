<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Pet Lover';

// Fetch all orders for this user (excluding Cancelled)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status != 'Cancelled' ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .order-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 1.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-processing {
            background: #fef3c7;
            color: #92400e;
        }

        .status-shipped {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .item-row {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem 0;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 1rem;
            object-fit: cover;
            background: #f9fafb;
        }

        .order-footer {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar (Reused from dashboard.php) -->
        <?php include 'user-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <button class="menu-toggle-btn" onclick="if(window.toggleUserSidebar) window.toggleUserSidebar();">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 style="font-family: 'Outfit';">My Purchase History</h1>
                <div class="user-mini-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=random"
                        class="mini-avatar">
                </div>
            </header>

            <div class="content-wrapper">
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 2rem;">
                        <i class="fa-solid fa-box-open" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1.5rem;"></i>
                        <h2 style="font-family: 'Outfit';">No orders found</h2>
                        <p style="color: #6b7280; margin-bottom: 2rem;">You haven't purchased anything yet.</p>
                        <a href="marketplace.php" class="btn btn-primary"
                            style="padding: 1rem 2.5rem; text-decoration: none;">Browse Marketplace</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <h3 style="font-family: 'Outfit'; margin-bottom: 0.25rem;">Order #
                                        <?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                                    </h3>
                                    <span style="color: #6b7280; font-size: 0.9rem;">Placed on
                                        <?php echo date('F d, Y • h:i A', strtotime($order['created_at'])); ?>
                                    </span>
                                </div>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </div>

                            <div class="order-items">
                                <?php
                                $itemsStmt = $pdo->prepare("
                                    SELECT oi.*, p.name, p.image_url 
                                    FROM order_items oi 
                                    JOIN products p ON oi.product_id = p.id 
                                    WHERE oi.order_id = ?
                                ");
                                $itemsStmt->execute([$order['id']]);
                                $items = $itemsStmt->fetchAll();

                                foreach ($items as $item):
                                    ?>
                                    <div class="item-row">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="item-image">
                                        <div style="flex: 1;">
                                            <h4 style="margin-bottom: 0.25rem;">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </h4>
                                            <span style="color: #6b7280; font-size: 0.9rem;">Quantity:
                                                <?php echo $item['quantity']; ?>
                                            </span>
                                        </div>
                                        <div style="text-align: right; font-weight: 700;">
                                            ₹
                                            <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-footer">
                                <div>
                                    <div style="font-size: 0.85rem; color: #6b7280;">Payment Method</div>
                                    <div style="font-weight: 600; font-size: 0.95rem; color: #10b981;">
                                        <i class="fa-solid fa-shield-check"></i> Razorpay (ID:
                                        <?php echo htmlspecialchars($order['payment_id']); ?>)
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 0.25rem;">Total Amount</div>
                                    <div style="font-size: 1.5rem; font-weight: 800; color: #111827; font-family: 'Outfit';">
                                        ₹
                                        <?php echo number_format($order['total_amount'], 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>