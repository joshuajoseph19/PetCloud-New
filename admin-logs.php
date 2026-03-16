<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

// --- Create Log Table if Missing ---
$pdo->exec("CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    target_type VARCHAR(50),
    target_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Mock some logs if empty for demo
$check = $pdo->query("SELECT COUNT(*) FROM admin_activity_logs")->fetchColumn();
if ($check == 0) {
    $pdo->exec("INSERT INTO admin_activity_logs (admin_name, action, target_type) VALUES 
        ('Admin', 'Approved Shop Application', 'Shop'),
        ('Admin', 'Blocked User account', 'User'),
        ('Admin', 'Updated Platform Commission', 'Settings')");
}

$logs = $pdo->query("SELECT * FROM admin_activity_logs ORDER BY created_at DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Activity Logs - Admin Panel</title>
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

        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table th {
            text-align: left;
            background: #f9fafb;
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
        }

        .log-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        .log-time {
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .log-action {
            font-weight: 600;
            color: #1e293b;
        }

        .log-target {
            background: #f1f5f9;
            color: #475569;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <h2 class="page-title">Administrative Audit Logs</h2>

        <div class="admin-card">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Administrator</th>
                        <th>Action Performed</th>
                        <th>Module</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="log-time">
                                <?php echo date('M d, H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td><strong>
                                    <?php echo htmlspecialchars($log['admin_name']); ?>
                                </strong></td>
                            <td class="log-action">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </td>
                            <td><span class="log-target">
                                    <?php echo htmlspecialchars($log['target_type']); ?>
                                </span></td>
                            <td style="color: #64748b; font-family: monospace;">
                                <?php echo $log['ip_address'] ?? '127.0.0.1'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>