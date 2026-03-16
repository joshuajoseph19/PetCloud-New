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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Adoption Approvals - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .pet-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 0.75rem;
            background: #eee;
        }

        .content {
            flex: 1;
        }

        .badges {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            background: #e5e7eb;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            justify-content: center;
            min-width: 120px;
        }

        .btn {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-approve {
            background: #10b981;
            color: white;
        }

        .btn-approve:hover {
            background: #059669;
        }

        .btn-reject {
            background: #fee2e2;
            color: #ef4444;
        }

        .btn-reject:hover {
            background: #fecaca;
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Adoption Approvals</h1>
            <a href="admin-dashboard.php" class="btn" style="background:white; color:#374151;">Back to Dashboard</a>
        </div>

        <?php if (empty($pending)): ?>
            <div class="card empty-state">
                <i class="fa-solid fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; color: #10b981;"></i>
                <h2>All Caught Up!</h2>
                <p>No pending adoption listings at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pending as $item): ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="pet-img">
                    <div class="content">
                        <div class="badges">
                            <span class="badge">
                                <?php echo ucfirst($item['pet_type']); ?>
                            </span>
                            <span class="badge">
                                <?php echo htmlspecialchars($item['gender']); ?>
                            </span>
                        </div>
                        <h2 style="margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($item['pet_name']); ?>
                        </h2>
                        <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem;">
                            Posted by <strong>
                                <?php echo htmlspecialchars($item['owner_name']); ?>
                            </strong> (
                            <?php echo htmlspecialchars($item['owner_email']); ?>)
                        </p>

                        <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                            <p style="font-size: 0.9rem; margin-bottom: 0.5rem;"><strong>Description:</strong>
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>
                            <p style="font-size: 0.9rem; color: #ef4444;"><strong>Reason:</strong>
                                <?php echo htmlspecialchars($item['reason_for_adoption']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="actions">
                        <a href="?action=approve&id=<?php echo $item['id']; ?>" class="btn btn-approve"><i
                                class="fa-solid fa-check"></i> Approve</a>
                        <a href="?action=reject&id=<?php echo $item['id']; ?>" class="btn btn-reject"><i
                                class="fa-solid fa-xmark"></i> Reject</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>