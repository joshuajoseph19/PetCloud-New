<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.html');
    exit;
}

$appId = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM shop_applications WHERE id = ?");
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    header('Location: admin-shop-approvals.php');
    exit;
}

// --- Handle Decision ---
if (isset($_POST['decision'])) {
    $status = $_POST['decision'] == 'approve' ? 'approved' : 'rejected';

    $pdo->beginTransaction();
    try {
        // Update application status
        $pdo->prepare("UPDATE shop_applications SET status = ? WHERE id = ?")->execute([$status, $appId]);

        if ($status == 'approved') {
            // Check if user already exists
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$app['email']]);
            $existingUser = $check->fetch();

            if ($existingUser) {
                // Update existing user to shop_owner
                $pdo->prepare("UPDATE users SET role = 'shop_owner' WHERE id = ?")->execute([$existingUser['id']]);
                $userId = $existingUser['id'];
            } else {
                // Create new user
                // Use provided hash or generate default default for seed data
                $password = !empty($app['password_hash']) ? $app['password_hash'] : password_hash('PetCloud123!', PASSWORD_DEFAULT);

                $sqlUser = "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'shop_owner')";
                $pdo->prepare($sqlUser)->execute([$app['full_name'], $app['email'], $password]);
                $userId = $pdo->lastInsertId();
            }

            // Create shop entry in shop_profiles or similar? (Admin requirement 5)
            // For now, many parts of the app look for shop info in shop_applications themselves.
            // But a 'shops' table is better for scale.
        }

        $pdo->commit();
        header("Location: admin-shop-approvals.php?msg=Status updated for " . $app['shop_name']);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "System error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Application -
        <?php echo htmlspecialchars($app['shop_name']); ?>
    </title>
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
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title {
            font-family: 'Outfit';
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
        }

        .application-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .admin-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid #e5e7eb;
            padding: 2rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 0.5rem;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .detail-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .detail-value {
            font-weight: 600;
            color: #111827;
        }

        .decision-box {
            position: sticky;
            top: 100px;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-approve {
            background: #10b981;
            color: white;
        }

        .btn-approve:hover {
            background: #059669;
        }

        .btn-reject {
            background: #ef4444;
            color: white;
        }

        .btn-reject:hover {
            background: #dc2626;
        }

        .btn-back {
            background: #f3f4f6;
            color: #4b5563;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <?php include 'admin-sidebar.php'; ?>
    <?php include 'admin-header.php'; ?>

    <main class="main-layout">
        <?php if (isset($error)): ?>
            <div
                style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid #fecaca;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
                <strong>Error:</strong>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <a href="admin-shop-approvals.php" class="btn-back"
                style="padding: 0.5rem; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <h2 class="page-title">Application Review</h2>
        </div>

        <div class="application-grid">
            <div class="admin-card">
                <div class="section-title">Shop Information</div>
                <div class="detail-row">
                    <div class="detail-label">Store Name</div>
                    <div class="detail-value" style="font-size: 1.25rem; color: var(--primary);">
                        <?php echo htmlspecialchars($app['shop_name']); ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Business Category</div>
                    <div class="detail-value"><span
                            style="background: #eef2ff; color: #4338ca; padding: 4px 12px; border-radius: 99px;">
                            <?php echo htmlspecialchars($app['shop_category']); ?>
                        </span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Reg. Number</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($app['business_reg']); ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Years in Business</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($app['years_in_business']); ?> Years
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Store Address</div>
                    <div class="detail-value">
                        <?php echo nl2br(htmlspecialchars($app['address'])); ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Description</div>
                    <div class="detail-value" style="font-weight: 400; line-height: 1.5; color: #4b5563;">
                        <?php echo nl2br(htmlspecialchars($app['description'])); ?>
                    </div>
                </div>

                <div class="section-title" style="margin-top: 3rem;">Owner Details</div>
                <div class="detail-row">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($app['full_name']); ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email Address</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($app['email']); ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone Number</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($app['phone']); ?>
                    </div>
                </div>
            </div>

            <div class="decision-box">
                <div class="admin-card" style="border: 2px solid #f1f5f9;">
                    <div class="section-title">Final Decision</div>
                    <p style="color: #64748b; font-size: 0.875rem; margin-bottom: 2rem;">Approving this application will
                        automatically grant shop owner permissions to the account and notify the applicant.</p>

                    <?php if ($app['status'] == 'pending'): ?>
                        <form method="POST">
                            <button type="submit" name="decision" value="approve" class="btn btn-approve">
                                <i class="fa-solid fa-check"></i> Approve & Activate
                            </button>
                            <button type="submit" name="decision" value="reject" class="btn btn-reject">
                                <i class="fa-solid fa-times"></i> Decline Application
                            </button>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center; padding: 1.5rem; background: #f8fafc; border-radius: 1rem;">
                            <div
                                style="font-weight: 800; color: <?php echo $app['status'] == 'approved' ? '#10b981' : '#ef4444'; ?>; font-size: 1.25rem; margin-bottom: 0.5rem;">
                                APPLICATION
                                <?php echo strtoupper($app['status']); ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #64748b;">Processed on
                                <?php echo date('M d, Y'); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <a href="admin-shop-approvals.php" class="btn btn-back"
                        style="margin-top: 1rem; margin-bottom: 0;">Back to List</a>
                </div>
            </div>
        </div>
    </main>
</body>

</html>