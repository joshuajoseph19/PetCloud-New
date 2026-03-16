<?php
session_start();
require_once 'db_connect.php';

// Check if admin (Basic check for now, can be expanded)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.html");
    exit();
}

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = $_GET['action'] == 'approve' ? 'active' : 'rejected';

    $stmt = $pdo->prepare("UPDATE adoption_listings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    header("Location: admin-adoption-approvals.php?msg=" . $status);
    exit();
}

// Fetch Pending
$stmt = $pdo->query("SELECT l.*, u.full_name as owner_name, u.email as owner_email 
                     FROM adoption_listings l 
                     LEFT JOIN users u ON l.user_id = u.id 
                     WHERE l.status = 'pending_approval' 
                     ORDER BY l.created_at DESC");
$pending = $stmt->fetchAll();
?>
<<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoption Approvals - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --sidebar: #111827;
            --bg: #f8fafc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #1e293b;
        }

        .main-layout {
            margin-left: 260px;
            padding: 2.5rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-family: 'Outfit';
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
        }

        .approval-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .approval-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
            display: flex;
            gap: 2rem;
            transition: 0.3s;
        }

        .approval-card:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .pet-media {
            flex-shrink: 0;
            width: 220px;
            height: 220px;
            border-radius: 1rem;
            overflow: hidden;
            background: #f1f5f9;
        }

        .pet-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pet-details {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .pet-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .pet-name {
            font-family: 'Outfit';
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .owner-info {
            font-size: 0.875rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .owner-info i { color: var(--primary); }

        .tag-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            background: #f1f5f9;
            color: #475569;
        }

        .description-box {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 1rem;
            font-size: 0.9375rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            border: 1px solid #f1f5f9;
        }

        .action-row {
            margin-top: auto;
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.2s;
            border: none;
        }

        .btn-approve {
            background: var(--primary);
            color: white;
        }

        .btn-approve:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-reject {
            background: #fee2e2;
            color: #ef4444;
        }

        .btn-reject:hover {
            background: #fecaca;
            transform: translateY(-2px);
        }

        .empty-state {
            background: white;
            border-radius: 2rem;
            padding: 5rem 2rem;
            text-align: center;
            border: 1px dashed #cbd5e1;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        @media (max-width: 1024px) {
            .main-layout { margin-left: 0; padding: 1.5rem; }
            .approval-card { flex-direction: column; }
            .pet-media { width: 100%; height: 250px; }
        }
    </style>
</head>

<body>

    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <div class="page-header">
            <h2 class="page-title">Adoption Listing Approvals</h2>
            <div class="badge" style="background: #fef3c7; color: #b45309;">
                <?php echo count($pending); ?> Pending Review
            </div>
        </div>

        <?php if (empty($pending)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <h2 style="font-family: 'Outfit'; margin-bottom: 0.5rem;">All Caught Up!</h2>
                <p style="color: #64748b;">There are no new adoption listings waiting for approval.</p>
            </div>
        <?php else: ?>
            <div class="approval-grid">
                <?php foreach ($pending as $item): ?>
                    <div class="approval-card">
                        <div class="pet-media">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="pet-img" alt="Pet Image">
                        </div>
                        <div class="pet-details">
                            <div class="pet-header">
                                <div>
                                    <h3 class="pet-name"><?php echo htmlspecialchars($item['pet_name']); ?></h3>
                                    <div class="owner-info">
                                        <i class="fa-solid fa-user-circle"></i>
                                        <span>Posted by <strong><?php echo htmlspecialchars($item['owner_name']); ?></strong> (<?php echo htmlspecialchars($item['owner_email']); ?>)</span>
                                    </div>
                                </div>
                                <div class="tag-row">
                                    <span class="badge"><?php echo ucfirst($item['pet_type']); ?></span>
                                    <span class="badge"><?php echo htmlspecialchars($item['gender']); ?></span>
                                </div>
                            </div>

                            <div class="description-box">
                                <p style="margin-bottom: 0.75rem;"><strong>Statement:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                                <p style="color: #ef4444; font-weight: 600;"><strong>Reason for Adoption:</strong> <?php echo htmlspecialchars($item['reason_for_adoption']); ?></p>
                            </div>

                            <div class="action-row">
                                <a href="?action=approve&id=<?php echo $item['id']; ?>" class="btn btn-approve">
                                    <i class="fa-solid fa-check"></i> Approve Listing
                                </a>
                                <a href="?action=reject&id=<?php echo $item['id']; ?>" class="btn btn-reject">
                                    <i class="fa-solid fa-xmark"></i> Reject
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>

</html>