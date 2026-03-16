<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

// --- Ensure table exists and Seed if empty ---
$pdo->exec("CREATE TABLE IF NOT EXISTS shop_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_name VARCHAR(255),
    full_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    shop_category VARCHAR(100),
    business_reg VARCHAR(100),
    years_in_business INT,
    address TEXT,
    description TEXT,
    password_hash VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$checkApps = $pdo->query("SELECT COUNT(*) FROM shop_applications")->fetchColumn();
if ($checkApps == 0) {
    $pdo->exec("INSERT INTO shop_applications (shop_name, full_name, email, shop_category, business_reg, years_in_business, address, description, status) VALUES 
        ('Paws & Whiskers', 'John Doe', 'john@paws.com', 'Pet Supplies', 'REG-12345', 3, '123 Pet Lane, NY', 'We specialize in organic pet food.', 'pending'),
        ('The Pet Emporium', 'Jane Smith', 'jane@emporium.com', 'Grooming', 'REG-99887', 5, '456 Tail St, LA', 'Modern grooming services for all breeds.', 'pending'),
        ('Happy Paws Clinic', 'Mike Ross', 'mike@happypaws.com', 'Healthcare', 'REG-55443', 10, '789 Bark Ave, CHI', 'Expert veterinary care since 2012.', 'approved')");
}

// --- Fetch Applications ---
$stmt = $pdo->query("SELECT * FROM shop_applications ORDER BY status ASC, applied_at DESC");
$apps = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shop Approvals - Admin Panel</title>
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
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Outfit';
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
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

        .status-pill {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-pending {
            background: #fef3c7;
            color: #b45309;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-view {
            background: #f3f4f6;
            color: #111827;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.2s;
        }

        .btn-view:hover {
            background: #e5e7eb;
        }

        .btn-delete {
            background: #fee2e2;
            color: #ef4444;
            margin-left: 0.5rem;
        }

        .btn-delete:hover {
            background: #fecaca;
            color: #dc2626;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <?php if (isset($_GET['msg'])): ?>
            <div
                style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
                <i class="fa-solid fa-check-circle" style="margin-right: 0.5rem;"></i>
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div
                style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #fecaca;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <div class="page-header">
            <h2 class="page-title">Shop Owner Applications</h2>
            <p style="color: #64748b;">Review and manage seller status for the PetCloud marketplace.</p>
        </div>

        <div class="admin-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Applicant</th>
                        <th>Category</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apps as $app): ?>
                        <tr>
                            <td><strong>
                                    <?php echo htmlspecialchars($app['shop_name']); ?>
                                </strong></td>
                            <td>
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars($app['full_name']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    <?php echo htmlspecialchars($app['email']); ?>
                                </div>
                            </td>
                            <td><span
                                    style="background: #eef2ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($app['shop_category']); ?>
                                </span></td>
                            <td style="color: #64748b;">
                                <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                            </td>
                            <td><span class="status-pill status-<?php echo strtolower($app['status']); ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span></td>
                            <td>
                                <a href="admin-view-shop.php?id=<?php echo $app['id']; ?>" class="btn-view">
                                    <i class="fa-solid fa-eye"></i> Review Application
                                </a>
                                <a href="admin-delete-shop-application.php?id=<?php echo $app['id']; ?>"
                                    class="btn-view btn-delete"
                                    onclick="return confirm('Are you sure you want to remove this shop owner application? This action cannot be undone.');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach;
                    if (empty($apps))
                        echo "<tr><td colspan='6' style='text-align:center; padding:3rem; color:#9ca3af;'>No applications found.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>