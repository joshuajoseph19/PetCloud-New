<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

// --- Handle Status Toggles ---
if (isset($_POST['toggle_status'])) {
    $shopId = $_POST['shop_id'];
    $newStatus = $_POST['current_status'] == 'approved' ? 'suspended' : 'approved';
    $pdo->prepare("UPDATE shop_applications SET status = ? WHERE id = ?")->execute([$newStatus, $shopId]);
    header("Location: admin-shops.php?msg=Shop status updated");
    exit;
}

// --- Fetch Approved/Suspended Shops ---
$shops = $pdo->query("SELECT * FROM shop_applications WHERE status IN ('approved', 'suspended') ORDER BY shop_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Managed Shops - Admin Panel</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap"
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

        .page-title {
            font-family: 'Outfit';
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2rem;
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

        .shop-badge {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-suspended {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-status {
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8125rem;
            transition: 0.2s;
        }

        .btn-suspend {
            background: #fee2e2;
            color: #ef4444;
        }

        .btn-activate {
            background: #dcfce7;
            color: #10b981;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <h2 class="page-title">Manage Marketplace Shops</h2>

        <div class="admin-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Owner</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shops as $shop): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #111827;">
                                    <?php echo htmlspecialchars($shop['shop_name']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 500;">Reg:
                                    <?php echo htmlspecialchars($shop['business_reg']); ?>
                                </div>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($shop['full_name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($shop['shop_category']); ?>
                            </td>
                            <td><span class="shop-badge status-<?php echo strtolower($shop['status']); ?>">
                                    <?php echo ucfirst($shop['status']); ?>
                                </span></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="shop_id" value="<?php echo $shop['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $shop['status']; ?>">
                                    <?php if ($shop['status'] == 'approved'): ?>
                                        <button type="submit" name="toggle_status" class="btn-status btn-suspend">
                                            <i class="fa-solid fa-ban"></i> Suspend Shop
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="toggle_status" class="btn-status btn-activate">
                                            <i class="fa-solid fa-rotate-right"></i> Reactivate
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                    if (empty($shops))
                        echo "<tr><td colspan='5' style='text-align:center; padding:3rem; color:#9ca3af;'>No approved shops yet.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>