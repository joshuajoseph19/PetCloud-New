<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

// --- Handle Status Updates ---
if (isset($_POST['update_adoption'])) {
    $requestId = $_POST['request_id'];
    $newStatus = $_POST['status'];

    // 1. Fetch the listing_id for this application before updating
    $stmt = $pdo->prepare("SELECT listing_id, status FROM adoption_applications WHERE id = ?");
    $stmt->execute([$requestId]);
    $app = $stmt->fetch();
    $oldStatus = $app['status'] ?? '';
    $listingId = $app['listing_id'] ?? null;

    // 2. Update Application Status
    $pdo->prepare("UPDATE adoption_applications SET status = ? WHERE id = ?")->execute([$newStatus, $requestId]);

    // 3. Sync Pet Listing Status
    if ($listingId) {
        if ($newStatus === 'approved') {
            // Mark as adopted
            $pdo->prepare("UPDATE adoption_listings SET status = 'adopted' WHERE id = ?")->execute([$listingId]);
        } elseif ($oldStatus === 'approved' && $newStatus !== 'approved') {
            // Rollback: If it was approved (and thus hidden) and now it's not, make it active again
            $pdo->prepare("UPDATE adoption_listings SET status = 'active' WHERE id = ?")->execute([$listingId]);
        }
    }

    header("Location: admin-adoptions.php?msg=Adoption status updated");
    exit;
}

// --- Fetch Adoption Applications ---
$stmt = $pdo->query("SELECT * FROM adoption_applications ORDER BY applied_at DESC");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Adoption Management - Admin Panel</title>
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

        .status-pill {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.7rem;
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

        .select-status {
            padding: 0.4rem;
            border-radius: 0.4rem;
            border: 1px solid #e5e7eb;
            font-size: 0.8rem;
            font-weight: 600;
            outline: none;
        }

        .btn-update {
            background: #111827;
            color: white;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 0.4rem;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <h2 class="page-title">Adoption Requests Management</h2>

        <div class="admin-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pet Info</th>
                        <th>Applicant Details</th>
                        <th>Living Situation</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Quick Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #111827;">
                                    <?php echo htmlspecialchars($req['pet_name']); ?>
                                </div>
                                <div
                                    style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($req['pet_category']); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars($req['applicant_name']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    <?php echo htmlspecialchars($req['applicant_email']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">M:
                                    <?php echo htmlspecialchars($req['applicant_phone']); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.8125rem; color: #4b5563; max-width: 200px;">
                                    <?php echo htmlspecialchars($req['living_situation']); ?>
                                </div>
                                <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 4px; font-weight: 600;">Other
                                    Pets:
                                    <?php echo $req['has_other_pets'] ? 'YES' : 'NO'; ?>
                                </div>
                            </td>
                            <td style="color: #64748b;">
                                <?php echo date('M d, Y', strtotime($req['applied_at'])); ?>
                            </td>
                            <td><span class="status-pill status-<?php echo strtolower($req['status']); ?>">
                                    <?php echo ucfirst($req['status']); ?>
                                </span></td>
                            <td>
                                <form method="POST" style="display: flex; gap: 0.4rem;">
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <select name="status" class="select-status">
                                        <option value="pending" <?php echo $req['status'] == 'pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="approved" <?php echo $req['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $req['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                    <button type="submit" name="update_adoption" class="btn-update">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                    if (empty($requests))
                        echo "<tr><td colspan='6' style='text-align:center; padding:3rem; color:#9ca3af;'>No adoption requests found.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>