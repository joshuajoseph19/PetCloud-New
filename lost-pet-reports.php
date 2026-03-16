<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all found reports for the owner's active lost alerts
$stmt = $pdo->prepare("
    SELECT fr.*, lpa.last_seen_location, p.pet_name, p.pet_image, u.full_name as reporter_name
    FROM found_pet_reports fr
    JOIN lost_pet_alerts lpa ON fr.alert_id = lpa.id
    JOIN user_pets p ON lpa.pet_id = p.id
    JOIN users u ON fr.user_id = u.id
    WHERE lpa.user_id = ? AND lpa.status = 'Active'
    ORDER BY fr.created_at DESC
");
$stmt->execute([$user_id]);
$reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Pet Reports - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .report-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            border: 1px solid #f3f4f6;
        }

        .pet-mini-img {
            width: 100px;
            height: 100px;
            border-radius: 1.25rem;
            object-fit: cover;
        }

        .report-content {
            flex: 1;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .reporter-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-radius: 1rem;
            font-size: 0.9rem;
        }

        .notes-box {
            background: #fffbef;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 0.5rem;
            font-style: italic;
            color: #92400e;
        }
    </style>
</head>

<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar (Simplified or use partial) -->
        <?php include 'user-sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <button class="menu-toggle-btn" onclick="if(window.toggleUserSidebar) window.toggleUserSidebar();">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 style="font-family:'Outfit'; font-size: 1.75rem;">Found Pet Reports</h1>
            </header>

            <div class="content-wrapper">
                <?php if (empty($reports)): ?>
                    <div
                        style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 2rem; border: 1px dashed #e2e8f0;">
                        <div style="font-size: 4rem; color: #f1f5f9; margin-bottom: 1.5rem;"><i
                                class="fa-solid fa-envelope-open-text"></i></div>
                        <h2 style="font-family:'Outfit'; color: #334155; margin-bottom: 1rem;">No reports received yet</h2>
                        <p style="color: #64748b; max-width: 400px; margin: 0 auto 2rem;">This page is where sightings of
                            *your* lost pets appear. People can only report sightings if you've broadcasted an alert for a
                            missing pet.</p>

                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <a href="mypets.php" class="btn"
                                style="background: #f1f5f9; color: #475569; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none;">Go
                                to My Pets</a>
                            <a href="dashboard.php" class="btn"
                                style="background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none;">Back
                                to Dashboard</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="reports-list">
                        <?php foreach ($reports as $report): ?>
                            <div class="report-card">
                                <img src="<?php echo htmlspecialchars($report['pet_image']); ?>" class="pet-mini-img">
                                <div class="report-content">
                                    <div class="report-header">
                                        <h3 style="font-family:'Outfit';">New Sightning of
                                            <?php echo htmlspecialchars($report['pet_name']); ?>!
                                        </h3>
                                        <span style="font-size: 0.8rem; color: #9ca3af;">
                                            <?php echo date('M d, Y h:i A', strtotime($report['created_at'])); ?>
                                        </span>
                                    </div>

                                    <div class="reporter-info">
                                        <i class="fa-solid fa-user-check" style="color: #3b82f6;"></i>
                                        <span>Reported by <strong>
                                                <?php echo htmlspecialchars($report['reporter_name']); ?>
                                            </strong></span>
                                        <span style="margin-left: auto; color: #64748b;"><i class="fa-solid fa-phone"></i>
                                            <?php echo htmlspecialchars($report['contact_info'] ?: 'No contact provided'); ?>
                                        </span>
                                    </div>

                                    <div style="margin-bottom: 1.5rem;">
                                        <p style="font-size: 0.95rem; margin-bottom: 0.5rem;"><i
                                                class="fa-solid fa-location-crosshairs"></i> <strong>Location:</strong>
                                            <?php echo htmlspecialchars($report['found_location']); ?>
                                        </p>
                                        <?php if ($report['notes']): ?>
                                            <div class="notes-box">
                                                "
                                                <?php echo htmlspecialchars($report['notes']); ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <form method="POST" action="dashboard.php" style="display: inline-block;">
                                        <input type="hidden" name="pet_id"
                                            value="<?php echo htmlspecialchars($report['pet_id']); ?>">
                                        <button type="submit" name="mark_as_found" class="btn btn-primary"
                                            style="padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 700;">
                                            <i class="fa-solid fa-check-double"></i> Mark as Safely Found
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>