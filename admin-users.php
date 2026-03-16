<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

// --- Handle User Actions ---
if (isset($_POST['action'])) {
    $userId = $_POST['user_id'];
    $adminName = $_SESSION['admin_name'] ?? 'Admin';
    if ($_POST['action'] == 'block') {
        $pdo->prepare("UPDATE users SET status = 'blocked' WHERE id = ?")->execute([$userId]);
        logAdminActivity($pdo, $adminName, "Blocked user ID #$userId", "User Management", $userId);
    } elseif ($_POST['action'] == 'unblock') {
        $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$userId]);
        logAdminActivity($pdo, $adminName, "Unblocked user ID #$userId", "User Management", $userId);
    }
    header("Location: admin-users.php?msg=Status updated");
    exit;
}

// --- Fetch Users with Filters ---
$roleFilter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($roleFilter) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}
if ($search) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin Panel</title>
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

        .filter-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            outline: none;
        }

        .role-select {
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
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

        .user-cell {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .role-badge {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .role-admin {
            background: #fee2e2;
            color: #991b1b;
        }

        .role-client {
            background: #dcfce7;
            color: #166534;
        }

        .role-shop_owner {
            background: #fef9c3;
            color: #854d0e;
        }

        .btn-action {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: white;
            cursor: pointer;
            color: #64748b;
            transition: 0.2s;
        }

        .btn-action:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-block {
            color: #ef4444;
            border-color: #fecaca;
        }

        .btn-block:hover {
            background: #fef2f2;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <div class="page-header">
            <h2 class="page-title">User Management</h2>
            <div style="color: #64748b; font-weight: 500;">
                <?php echo count($users); ?> Total Users Registered
            </div>
        </div>

        <form class="filter-bar" method="GET">
            <i class="fa-solid fa-filter" style="color: #9ca3af;"></i>
            <input type="text" name="search" placeholder="Search by name or email..." class="search-input"
                value="<?php echo htmlspecialchars($search); ?>">
            <select name="role" class="role-select">
                <option value="">All Roles</option>
                <option value="client" <?php echo $roleFilter == 'client' ? 'selected' : ''; ?>>Clients</option>
                <option value="shop_owner" <?php echo $roleFilter == 'shop_owner' ? 'selected' : ''; ?>>Shop Owners
                </option>
                <option value="adopter" <?php echo $roleFilter == 'adopter' ? 'selected' : ''; ?>>Adopters</option>
                <option value="admin" <?php echo $roleFilter == 'admin' ? 'selected' : ''; ?>>Admins</option>
            </select>
            <button type="submit"
                style="background: var(--sidebar); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer;">Apply
                Filter</button>
        </form>

        <div class="admin-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=random"
                                        class="user-avatar">
                                    <div>
                                        <div style="font-weight: 700; color: #111827;">
                                            <?php echo htmlspecialchars($user['full_name']); ?>
                                        </div>
                                        <div style="color: #64748b; font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo str_replace('_', ' ', $user['role']); ?>
                                </span></td>
                            <td style="color: #64748b;">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <span
                                    style="font-weight: 600; color: <?php echo ($user['status'] ?? 'active') == 'active' ? '#10b981' : '#ef4444'; ?>">
                                    <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn-action" title="Edit Profile"><i
                                            class="fa-solid fa-pen-to-square"></i></button>
                                    <button class="btn-action" title="Reset Password"><i
                                            class="fa-solid fa-key"></i></button>
                                    <?php if (($user['status'] ?? 'active') == 'active'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="action" value="block" class="btn-action btn-block"
                                                title="Block User"><i class="fa-solid fa-ban"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="action" value="unblock" class="btn-action"
                                                style="color: #10b981; border-color: #10b981;" title="Unblock User"><i
                                                    class="fa-solid fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>