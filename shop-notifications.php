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

// Mark all as read
if (isset($_GET['clear'])) {
    $stmt = $pdo->prepare("UPDATE shop_notifications SET is_read = 1 WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    header("Location: shop-notifications.php");
    exit();
}

// Fetch Notifications
$stmt = $pdo->prepare("SELECT * FROM shop_notifications WHERE shop_id = ? ORDER BY created_at DESC");
$stmt->execute([$shop_id]);
$notifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Notifications - PetCloud</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            font-family: 'Outfit';
            font-size: 1.75rem;
        }

        .notif-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notif-item {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            border: 1px solid #e5e7eb;
            transition: 0.2s;
            position: relative;
        }

        .notif-item.unread {
            border-left: 4px solid var(--primary);
            background: #fefeff;
        }

        .notif-item.unread::after {
            content: '';
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
        }

        .notif-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        .icon-order {
            background: #eef2ff;
            color: #4f46e5;
        }

        .icon-review {
            background: #fff7ed;
            color: #f59e0b;
        }

        .icon-stock {
            background: #fef2f2;
            color: #ef4444;
        }

        .notif-content {
            flex: 1;
        }

        .notif-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .notif-msg {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.4;
        }

        .notif-time {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.5rem;
        }

        .btn-clear {
            background: transparent;
            border: 1px solid #e5e7eb;
            padding: 0.65rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-clear:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }
    </style>
</head>

<body>

    <?php include 'shop-sidebar.php'; ?>
    <?php include 'shop-header.php'; ?>

    <main class="main-wrapper">
        <div class="page-header">
            <div>
                <h2>Shop Activity</h2>
                <p style="color: #64748b;">Keep track of orders, reviews, and stock alerts.</p>
            </div>
            <?php if (!empty($notifications)): ?>
                <a href="shop-notifications.php?clear=all" class="btn-clear">Mark All as Read</a>
            <?php endif; ?>
        </div>

        <div class="notif-list">
            <?php if (empty($notifications)): ?>
                <div
                    style="background: white; border-radius: 1.5rem; padding: 5rem; text-align: center; border: 1px dashed #cbd5e1;">
                    <i class="fa-regular fa-bell-slash" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                    <p style="color: #64748b;">No notifications yet. Activity will appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n):
                    $iconClass = "icon-order";
                    $icon = "fa-bag-shopping";
                    if ($n['type'] == 'review') {
                        $iconClass = "icon-review";
                        $icon = "fa-star";
                    }
                    if ($n['type'] == 'stock') {
                        $iconClass = "icon-stock";
                        $icon = "fa-triangle-exclamation";
                    }
                    ?>
                    <div class="notif-item <?php echo !$n['is_read'] ? 'unread' : ''; ?>">
                        <div class="notif-icon <?php echo $iconClass; ?>"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                        <div class="notif-content">
                            <div class="notif-title">
                                <?php echo htmlspecialchars($n['type'] == 'order' ? 'New Order Received' : ($n['type'] == 'review' ? 'New Review Posted' : 'Low Stock Alert')); ?>
                            </div>
                            <div class="notif-msg">
                                <?php echo htmlspecialchars($n['message']); ?>
                            </div>
                            <div class="notif-time">
                                <?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>