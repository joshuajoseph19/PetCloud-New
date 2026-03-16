<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

$success = "";
if (isset($_POST['send_notification'])) {
    $target = $_POST['target_role']; // all, client, shop_owner
    $message = $_POST['message'];
    $title = $_POST['title'];

    // For now, we store in a general notifications table
    // Creating the table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS platform_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        target_role VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->prepare("INSERT INTO platform_notifications (title, message, target_role) VALUES (?, ?, ?)");
    if ($stmt->execute([$title, $message, $target])) {
        $success = "Announcement broadcasted successfully to all " . ($target == 'all' ? 'users' : $target . 's') . "!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Announcements - Admin Panel</title>
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
            padding: 2rem;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
            transition: 0.2s;
            font-family: inherit;
        }

        .form-input:focus,
        .form-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .btn-send {
            background: #111827;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-send:hover {
            background: #000;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <h2 class="page-title">Send Platform Announcement</h2>

        <div class="admin-card">
            <?php if ($success): ?>
                <div class="alert-success"><i class="fa-solid fa-circle-check"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Notification Title</label>
                    <input type="text" name="title" class="form-input"
                        placeholder="e.g. Scheduled Maintenance or New Features" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Audience</label>
                    <select name="target_role" class="form-select">
                        <option value="all">Everyone (All Users)</option>
                        <option value="client">Clients Only</option>
                        <option value="shop_owner">Shop Owners Only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Message Content</label>
                    <textarea name="message" class="form-textarea" rows="6"
                        placeholder="Write your announcement message here..." required></textarea>
                </div>

                <button type="submit" name="send_notification" class="btn-send">
                    <i class="fa-solid fa-paper-plane"></i> Broadcast Notification
                </button>
            </form>
        </div>
    </main>
</body>

</html>