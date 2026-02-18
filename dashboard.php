<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Pet Lover';
$user_pic = $_SESSION['profile_pic'] ?? 'images/default_user.png';

// --- Time-based Greeting Logic (IST) ---
date_default_timezone_set('Asia/Kolkata');
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good Afternoon";
} elseif ($hour >= 17 && $hour < 21) {
    $greeting = "Good Evening";
} else {
    $greeting = "Good Night";
}

// Handle Actions (Mark as Done / Defer / Cancel Appointment)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $reminder_id = $_POST['reminder_id'];
        if ($_POST['action'] == 'complete') {
            $pdo->prepare("UPDATE health_reminders SET status = 'completed' WHERE id = ? AND user_id = ?")->execute([$reminder_id, $user_id]);
        } elseif ($_POST['action'] == 'defer') {
            $pdo->prepare("UPDATE health_reminders SET status = 'deferred', due_at = DATE_ADD(due_at, INTERVAL 1 HOUR) WHERE id = ? AND user_id = ?")->execute([$reminder_id, $user_id]);
        }
    } elseif (isset($_POST['cancel_appointment'])) {
        $appt_id = $_POST['appointment_id'];
        // Cancel appointment safely
        $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND user_id = ?")->execute([$appt_id, $user_id]);

        // Refresh to reflect changes
        header("Location: dashboard.php");
        exit();
    } elseif (isset($_POST['listing_action'])) {
        $lid = $_POST['listing_id'];
        $action = $_POST['listing_action'];

        // Verify ownership
        $check = $pdo->prepare("SELECT id FROM adoption_listings WHERE id = ? AND user_id = ?");
        $check->execute([$lid, $user_id]);
        if ($check->fetch()) {
            if ($action == 'delete') {
                $pdo->prepare("DELETE FROM adoption_listings WHERE id = ?")->execute([$lid]);
            } elseif ($action == 'mark_adopted') {
                $pdo->prepare("UPDATE adoption_listings SET status = 'adopted' WHERE id = ?")->execute([$lid]);
            }
            header("Location: dashboard.php?msg=success");
            exit();
        }
    }
}

// Fetch Latest Pending Reminder (for Hero)
$reminderStmt = $pdo->prepare("SELECT * FROM health_reminders WHERE user_id = ? AND status = 'pending' ORDER BY due_at ASC LIMIT 1");
$reminderStmt->execute([$user_id]);
$currentReminder = $reminderStmt->fetch();

// Fetch Feeding Schedules (Filtered for Today)
$feedStmt = $pdo->prepare("
    SELECT fs.*, p.pet_name 
    FROM feeding_schedules fs 
    LEFT JOIN user_pets p ON fs.pet_id = p.id 
    WHERE fs.user_id = ? 
    ORDER BY fs.feeding_time ASC
");
$feedStmt->execute([$user_id]);
$allSchedules = $feedStmt->fetchAll();

// Filter for Today
$feedingSchedules = [];
$todayDay = date('D'); // Mon, Tue...
foreach ($allSchedules as $fs) {
    if (isset($fs['days_of_week'])) {
        $days = json_decode($fs['days_of_week'] ?? '[]');
        // If days is null/empty (legacy) assume daily, otherwise check day
        if (!is_array($days) || empty($days) || in_array($todayDay, $days)) {
             $feedingSchedules[] = $fs;
        }
    } else {
        // Handle legacy case if column missing (shouldn't happen)
        $feedingSchedules[] = $fs;
    }
}

// Fetch Real Daily Tasks
$tasksStmt = $pdo->prepare("SELECT * FROM daily_tasks WHERE user_id = ? AND task_date = CURDATE()");
$tasksStmt->execute([$user_id]);
$dailyTasks = $tasksStmt->fetchAll();

// Fetch Top 3 Health Reminders (for Card)
$healthStmt = $pdo->prepare("SELECT * FROM health_reminders WHERE user_id = ? AND status = 'pending' ORDER BY due_at ASC LIMIT 3");
$healthStmt->execute([$user_id]);
$upcomingHealth = $healthStmt->fetchAll();

// Fetch Nearby Lost Pets
$userLocStmt = $pdo->prepare("SELECT location FROM users WHERE id = ?");
$userLocStmt->execute([$user_id]);
$userLoc = $userLocStmt->fetchColumn() ?? '';
$city = trim(explode(',', $userLoc)[0]);

$nearbyLostPets = [];
if ($city) {
    // Include self in results so user sees their own report instantly
    $lostStmt = $pdo->prepare("
        SELECT lpa.*, p.pet_name, p.pet_breed, p.pet_image, u.full_name as owner_name 
        FROM lost_pet_alerts lpa
        JOIN user_pets p ON lpa.pet_id = p.id
        JOIN users u ON lpa.user_id = u.id
        WHERE lpa.status = 'Active' 
        AND (lpa.last_seen_location LIKE ? OR ? LIKE CONCAT('%', lpa.last_seen_location, '%'))
        ORDER BY lpa.created_at DESC
    ");
    $lostStmt->execute(["%$city%", $userLoc]);
    $nearbyLostPets = $lostStmt->fetchAll();

    // Also fetch general found pets (strays) - include self
    $strayStmt = $pdo->prepare("
        SELECT * FROM general_found_pets 
        WHERE status = 'Active' 
        AND (found_location LIKE ? OR ? LIKE CONCAT('%', found_location, '%'))
        ORDER BY created_at DESC
    ");
    $strayStmt->execute(["%$city%", $userLoc]);
    $nearbyStrays = $strayStmt->fetchAll();

    // FALLBACK: If no local results, show any active reports (Global)
    if (empty($nearbyLostPets) && empty($nearbyStrays)) {
        $lostStmt = $pdo->query("SELECT lpa.*, p.pet_name, p.pet_breed, p.pet_image, u.full_name as owner_name FROM lost_pet_alerts lpa JOIN user_pets p ON lpa.pet_id = p.id JOIN users u ON lpa.user_id = u.id WHERE lpa.status = 'Active' ORDER BY lpa.created_at DESC LIMIT 5");
        $nearbyLostPets = $lostStmt->fetchAll();

        $strayStmt = $pdo->query("SELECT * FROM general_found_pets WHERE status = 'Active' ORDER BY created_at DESC LIMIT 5");
        $nearbyStrays = $strayStmt->fetchAll();
    }
}

// Handle Mark as Found from dashboard
if (isset($_POST['mark_as_found'])) {
    $pet_id = $_POST['pet_id'];
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE user_pets SET status = 'Active' WHERE id = ? AND user_id = ?")->execute([$pet_id, $user_id]);
    $pdo->prepare("UPDATE lost_pet_alerts SET status = 'Resolved' WHERE pet_id = ? AND status = 'Active'")->execute([$pet_id]);
    $pdo->commit();
    header("Location: dashboard.php");
    exit();
}

// Fetch Found Reports count for owner
$reportsCountStmt = $pdo->prepare("
    SELECT COUNT(*) FROM found_pet_reports fr
    JOIN lost_pet_alerts lpa ON fr.alert_id = lpa.id
    WHERE lpa.user_id = ? AND lpa.status = 'Active'
");
$reportsCountStmt->execute([$user_id]);
$foundReportsCount = $reportsCountStmt->fetchColumn();

// Fetch User's Adoption Listings
$adoptionStmt = $pdo->prepare("SELECT * FROM adoption_listings WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC");
$adoptionStmt->execute([$user_id]);
$myAdoptions = $adoptionStmt->fetchAll();

// Default if no reminder
if (!$currentReminder) {
    $currentReminder = [
        'id' => 0,
        'pet_name' => 'Your pets',
        'message' => 'No active health alerts today. Keep up the great care!',
        'due_at' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PetCloud</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="dashboard-page">

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'user-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="dashboard-search" placeholder="Search for pets, appointments, or tips...">
                </div>
                <div class="header-actions">
                    <div class="notif-wrapper" style="position: relative;">
                        <button type="button" class="icon-btn" id="notif-trigger"
                            style="border: none; background: #f1f5f9; cursor: pointer;">
                            <i class="fa-regular fa-bell"></i>
                            <span class="notification-dot"
                                style="background: #3b82f6; position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; border-radius: 50%; border: 2px solid #fff;"></span>
                        </button>
                        <div class="notif-dropdown" id="notif-dropdown"
                            style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 320px; background: white; border-radius: 1rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #f1f5f9; z-index: 9999; overflow: hidden;">
                            <div class="notif-header"
                                style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="font-family: 'Outfit'; font-size: 1rem; font-weight: 700; margin: 0;">
                                    Notifications</h3>
                                <span style="font-size: 0.75rem; color: #3b82f6; font-weight: 600;">3 New</span>
                            </div>
                            <div class="notif-list" style="max-height: 350px; overflow-y: auto;">
                                <a href="schedule.php" class="notif-item"
                                    style="padding: 1rem 1.25rem; display: flex; gap: 1rem; border-bottom: 1px solid #f8fafc; text-decoration: none; color: inherit; transition: background 0.2s;">
                                    <div class="notif-icon"
                                        style="width: 36px; height: 36px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa-solid fa-calendar-check"></i></div>
                                    <div class="notif-content">
                                        <div class="notif-title"
                                            style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Upcoming
                                            Appointment</div>
                                        <div class="notif-desc"
                                            style="font-size: 0.75rem; color: #64748b; line-height: 1.4;">Leo's
                                            vaccination scheduled for tomorrow at 10 AM.</div>
                                        <div class="notif-time"
                                            style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.5rem;">2 hours ago
                                        </div>
                                    </div>
                                </a>
                                <a href="smart-feeder.php" class="notif-item"
                                    style="padding: 1rem 1.25rem; display: flex; gap: 1rem; border-bottom: 1px solid #f8fafc; text-decoration: none; color: inherit; transition: background 0.2s;">
                                    <div class="notif-icon"
                                        style="width: 36px; height: 36px; border-radius: 50%; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa-solid fa-bone"></i></div>
                                    <div class="notif-content">
                                        <div class="notif-title"
                                            style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Smart Feeder
                                            Alert</div>
                                        <div class="notif-desc"
                                            style="font-size: 0.75rem; color: #64748b; line-height: 1.4;">Tank's lunch
                                            successfully dispensed (45g).</div>
                                        <div class="notif-time"
                                            style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.5rem;">4 hours ago
                                        </div>
                                    </div>
                                </a>
                                <a href="lost-pet-reports.php" class="notif-item"
                                    style="padding: 1rem 1.25rem; display: flex; gap: 1rem; border-bottom: 1px solid #f8fafc; text-decoration: none; color: inherit; transition: background 0.2s;">
                                    <div class="notif-icon"
                                        style="width: 36px; height: 36px; border-radius: 50%; background: #fff1f2; color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa-solid fa-bullhorn"></i></div>
                                    <div class="notif-content">
                                        <div class="notif-title"
                                            style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Lost Pet
                                            Nearby</div>
                                        <div class="notif-desc"
                                            style="font-size: 0.75rem; color: #64748b; line-height: 1.4;">A report was
                                            filed 2km away from your location.</div>
                                        <div class="notif-time"
                                            style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.5rem;">Yesterday
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="notif-footer"
                                style="padding: 0.75rem; text-align: center; background: #f8fafc;">
                                <a href="#"
                                    style="font-size: 0.8rem; font-weight: 600; color: #3b82f6; text-decoration: none;">See
                                    all activities</a>
                            </div>
                        </div>
                    </div>
                    <a href="mypets.php" class="btn"
                        style="background: #4b5e71; color: white; padding: 0.75rem 1.75rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px;">
                        ADD A PET
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <!-- Lost Pet Alert Banner -->
                <?php if (!empty($nearbyLostPets)): ?>
                    <div class="lost-pet-alert-banner"
                        style="background: #fff1f2; border: 1px solid #fecaca; border-radius: 1.5rem; padding: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem; animation: pulse 2s infinite;">
                        <div
                            style="background: #ef4444; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="font-family: 'Outfit'; color: #991b1b; margin-bottom: 0.25rem;">Lost Pet Alert
                                Nearby!</h3>
                            <p style="color: #b91c1c; font-size: 0.9rem;">A
                                <strong><?php echo htmlspecialchars($nearbyLostPets[0]['pet_breed']); ?></strong> named
                                <strong><?php echo htmlspecialchars($nearbyLostPets[0]['pet_name']); ?></strong> was last
                                seen near <?php echo htmlspecialchars($nearbyLostPets[0]['last_seen_location']); ?>. Please
                                keep an eye out!</p>
                        </div>
                        <a href="#lost-pets-section" class="btn"
                            style="background: #ef4444; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 700; text-decoration: none;">Help
                            Find</a>
                    </div>
                    <style>
                        @keyframes pulse {
                            0% {
                                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
                            }

                            70% {
                                box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
                            }

                            100% {
                                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
                            }
                        }
                    </style>
                <?php endif; ?>

                <!-- Hero Section (matching Image 0) -->
                <section class="dashboard-hero"
                    style="background: url('images/dashboard_hero_v3.png'); background-size: cover; background-position: center; padding: 0; border-radius: 2.5rem; margin-bottom: 3rem; position: relative; overflow: hidden; box-shadow: var(--shadow-premium); min-height: 380px; display: flex; align-items: flex-end;">

                    <!-- Glassmorphism Box Overlay (Image 0 style) -->
                    <div class="hero-box-overlay"
                        style="background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(12px); width: 100%; padding: 4rem 3.5rem; border-radius: 0 0 2.5rem 2.5rem; border-top: 1px solid rgba(255,255,255,0.3);">
                        <h1
                            style="font-size: 4.2rem; font-family: 'Outfit'; font-weight: 800; color: #0f172a; margin-bottom: 0.75rem; letter-spacing: -3px; line-height: 1;">
                            <?php echo $greeting; ?>,
                            <?php echo htmlspecialchars(strtolower(explode(' ', $user_name)[0])); ?>!
                        </h1>
                        <p
                            style="font-size: 1.35rem; font-weight: 500; color: #1e293b; max-width: 700px; line-height: 1.4; opacity: 0.9;">
                            <?php echo htmlspecialchars($currentReminder['pet_name'] . ' ' . $currentReminder['message']); ?>
                        </p>
                    </div>

                    <!-- Decorative Paw Print (Image 0 style) -->
                    <div
                        style="position: absolute; right: 2rem; top: 2rem; opacity: 0.15; font-size: 12rem; color: #0f172a; pointer-events: none;">
                        <i class="fa-solid fa-paw"></i>
                    </div>
                </section>

                <!-- My Family Section (New) -->
                <section class="my-family-section" style="margin-bottom: 2.5rem;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 style="font-family: 'Outfit'; font-size: 1.5rem;">My Family</h2>
                        <a href="mypets.php"
                            style="color: #3b82f6; text-decoration: none; font-weight: 600; font-size: 0.9rem;">View
                            All</a>
                    </div>
                    <div
                        style="display: flex; gap: 1.5rem; overflow-x: auto; padding-bottom: 1rem; scrollbar-width: none;">
                        <?php
                        $petsStmt = $pdo->prepare("SELECT * FROM user_pets WHERE user_id = ?");
                        $petsStmt->execute([$user_id]);
                        $myPets = $petsStmt->fetchAll();
                        foreach ($myPets as $pet):
                            $isLost = ($pet['status'] === 'Lost');
                            $cardBorder = $isLost ? '2px solid #ef4444' : '1px solid #f3f4f6';
                            $statusLabel = $isLost ? '<span style="background:#fee2e2; color:#ef4444; font-size:0.65rem; padding:2px 6px; border-radius:10px; font-weight:700;">LOST</span>' : '';
                            ?>
                            <div class="mini-pet-card"
                                style="min-width: 140px; background: white; padding: 1rem; border-radius: 1.25rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border: <?php echo $cardBorder; ?>; position: relative;">
                                <?php echo $statusLabel; ?>
                                <img src="<?php echo htmlspecialchars($pet['pet_image']); ?>"
                                    style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-bottom: 0.75rem; border: 3px solid #f3f4f6; filter: <?php echo $isLost ? 'grayscale(0.5)' : 'none'; ?>;">
                                <h4 style="font-size: 0.9rem; margin-bottom: 0.15rem;">
                                    <?php echo htmlspecialchars($pet['pet_name']); ?>
                                </h4>
                                <p style="font-size: 0.7rem; color: #9ca3af; margin-bottom: 0.75rem;">
                                    <?php echo htmlspecialchars($pet['pet_breed']); ?>
                                </p>

                                <?php if ($isLost): ?>
                                    <form method="POST">
                                        <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                        <button type="submit" name="mark_as_found" class="btn"
                                            style="background: #10b981; color: white; font-size: 0.65rem; padding: 0.4rem 0.8rem; border-radius: 0.5rem; width: 100%; font-weight: 700; border: none; cursor: pointer;">Found!</button>
                                    </form>
                                <?php else: ?>
                                    <button
                                        onclick="openLostModal(<?php echo $pet['id']; ?>, '<?php echo htmlspecialchars($pet['pet_name']); ?>')"
                                        class="btn"
                                        style="background: #f3f4f6; color: #4b5563; font-size: 0.65rem; padding: 0.4rem 0.8rem; border-radius: 0.5rem; width: 100%; font-weight: 700; border: none; cursor: pointer;">Mark
                                        Lost</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <a href="mypets.php"
                            style="min-width: 140px; background: #f9fafb; border: 2px dashed #e5e7eb; border-radius: 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: #9ca3af;">
                            <i class="fa-solid fa-plus" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                            <span style="font-size: 0.8rem; font-weight: 600;">Add Pet</span>
                        </a>
                    </div>
                </section>

                <div class="dashboard-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <!-- Column 1: Quick Actions & Schedule -->
                    <div class="grid-col-left">
                        <!-- Quick Actions Grid -->


                        <div class="card feeding-schedule-card"
                            style="background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                            <div class="card-header"
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                <div class="icon-title" style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="icon-yellow"
                                        style="width: 40px; height: 40px; background: #fef3c7; color: #d97706; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-utensils"></i>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 1rem;">Feeding Schedule</h4>
                                        <span class="text-xs text-muted"
                                            style="font-size: 0.75rem; color: #6b7280;">Today,
                                            <?php echo date('M d'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="schedule-list">
                                <?php if (empty($feedingSchedules)): ?>
                                    <div style="text-align: center; padding: 1rem; color: #9ca3af; font-size: 0.85rem;">
                                        No schedules set.
                                        <a href="feeding-manager.php"
                                            style="color: #3b82f6; display: block; margin-top: 0.5rem;">Add Routine</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($feedingSchedules as $schedule): ?>
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6;">
                                            <div>
                                                <span
                                                    style="font-size: 0.875rem; display: block; font-weight: 500;">
                                                    <?php echo htmlspecialchars($schedule['meal_name']); ?>
                                                    <?php if(!empty($schedule['pet_name'])): ?>
                                                        <span style="font-size:0.75rem; color:#9ca3af; font-weight:400; margin-left:4px;">(<?php echo htmlspecialchars($schedule['pet_name']); ?>)</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span
                                                    style="font-size: 0.75rem; color: #6b7280;"><?php echo htmlspecialchars($schedule['food_description']); ?></span>
                                            </div>
                                            <span style="font-size: 0.875rem; color: #10b981; font-weight: 600;">
                                                <?php echo date('g:i A', strtotime($schedule['feeding_time'])); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Upcoming Appointments Section -->
                         <div class="card appointments-card" 
                            style="background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 2rem;">
                            <div class="card-header"
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                <div class="icon-title" style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="icon-purple"
                                        style="width: 40px; height: 40px; background: #f3e8ff; color: #9333ea; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-stethoscope"></i>
                                    </div>
                                    <h4 style="font-size: 1rem;">Upcoming Visits</h4>
                                </div>
                                <a href="schedule.php"
                                    style="font-size: 0.75rem; color: #3b82f6; text-decoration: none;">New Booking</a>
                            </div>

                            <?php
                            // Fetch upcoming appointments
                            $apptStmt = $pdo->prepare("
                                SELECT a.*, h.name as hospital_name 
                                FROM appointments a 
                                LEFT JOIN hospitals h ON a.hospital_id = h.id 
                                WHERE a.user_id = ? AND a.status != 'cancelled' 
                                ORDER BY a.appointment_date ASC, a.appointment_time ASC 
                                LIMIT 3
                            ");
                            $apptStmt->execute([$user_id]);
                            $appointments = $apptStmt->fetchAll();

                            if (empty($appointments)): ?>
                                    <div style="text-align: center; padding: 2rem 0; color: #9ca3af;">
                                        <i class="fa-regular fa-calendar-xmark"
                                            style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                        <p style="font-size: 0.875rem;">No upcoming appointments</p>
                                    </div>
                            <?php else: ?>
                                    <div class="appointments-list">
                                        <?php foreach ($appointments as $appt):
                                            $apptDate = new DateTime($appt['appointment_date']);
                                            $formattedDate = $apptDate->format('M d');
                                            $formattedTime = date('g:i A', strtotime($appt['appointment_time']));
                                            ?>
                                                <div class="appt-item"
                                                    style="display:flex; align-items:center; gap:1rem; padding: 1rem; border: 1px solid #f3f4f6; border-radius: 0.75rem; margin-bottom: 0.75rem;">
                                                    <!-- Date Box -->
                                                    <div style="background:#f8fafc; padding:0.5rem 0.75rem; border-radius:0.5rem; text-align:center; min-width:60px;">
                                                        <div style="font-weight:700; color:#334155; font-size:1rem;"><?php echo $apptDate->format('d'); ?></div>
                                                        <div style="font-size:0.7rem; color:#64748b; text-transform:uppercase;"><?php echo $apptDate->format('M'); ?></div>
                                                    </div>
                                            
                                                    <!-- Info -->
                                                    <div style="flex:1;">
                                                        <h5 style="margin:0; font-size:0.95rem; color:#1e293b;"><?php echo htmlspecialchars($appt['service_type']); ?> for <?php echo htmlspecialchars($appt['pet_name']); ?></h5>
                                                        <div style="font-size:0.8rem; color:#64748b; margin-top:0.25rem;">
                                                            <i class="fa-solid fa-location-dot" style="color:#cbd5e1; margin-right:4px;"></i> 
                                                            <?php echo htmlspecialchars($appt['hospital_name'] ?? 'PetCloud Partner'); ?>
                                                        </div>
                                                    </div>

                                                    <!-- Time -->
                                                    <div style="font-size:0.85rem; font-weight:600; color:#9333ea;">
                                                        <?php echo $formattedTime; ?>
                                                    </div>

                                                    <!-- Delete Action -->
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');" style="margin-left:auto;">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                                        <input type="hidden" name="cancel_appointment" value="1">
                                                        <button type="submit" 
                                                            style="background:white; border:1px solid #fee2e2; cursor:pointer; color:#ef4444; width:32px; height:32px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; transition:0.2s;"
                                                            onmouseover="this.style.background='#fee2e2'"
                                                            onmouseout="this.style.background='white'">
                                                            <i class="fa-solid fa-trash-can" style="font-size:0.9rem;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                        <?php endforeach; ?>
                                    </div>
                            <?php endif; ?>
                        </div>

                        <!-- My Adoption Listings Section -->
                        <?php if (!empty($myAdoptions)): ?>
                            <div class="card adoption-status-card" 
                                style="background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 2rem; border-left: 4px solid #10b981;">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                    <div class="icon-title" style="display: flex; align-items: center; gap: 1rem;">
                                        <div class="icon-green" style="width: 40px; height: 40px; background: #d1fae5; color: #10b981; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-heart-pulse"></i>
                                        </div>
                                        <h4 style="font-size: 1rem;">My Adoption Listings</h4>
                                    </div>
                                    <a href="pet-rehoming.php" style="font-size: 0.75rem; color: #3b82f6; text-decoration: none;">Manage All</a>
                                </div>

                                <div class="adoption-list">
                                    <?php foreach ($myAdoptions as $pet): ?>
                                            <div class="adoption-item" style="display:flex; align-items:center; gap:1rem; padding: 1rem; border: 1px solid #f3f4f6; border-radius: 0.75rem; margin-bottom: 0.75rem;">
                                                <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" style="width: 45px; height: 45px; border-radius: 0.5rem; object-fit: cover;">
                                                <div style="flex:1;">
                                                    <h5 style="margin:0; font-size:0.95rem; color:#1e293b;"><?php echo htmlspecialchars($pet['pet_name']); ?></h5>
                                                    <span style="font-size:0.8rem; color:#64748b;"><?php echo ucfirst($pet['pet_type']); ?> • <?php echo htmlspecialchars($pet['breed']); ?></span>
                                                </div>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <form method="POST">
                                                        <input type="hidden" name="listing_id" value="<?php echo $pet['id']; ?>">
                                                        <input type="hidden" name="listing_action" value="mark_adopted">
                                                        <button type="submit" title="Mark Adopted" style="background:#d1fae5; border:none; cursor:pointer; color:#10b981; width:30px; height:30px; border-radius:0.4rem; display:flex; align-items:center; justify-content:center;">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" onsubmit="return confirm('Remove this listing from adoption?');">
                                                        <input type="hidden" name="listing_id" value="<?php echo $pet['id']; ?>">
                                                        <input type="hidden" name="listing_action" value="delete">
                                                        <button type="submit" title="Cancel Adoption" style="background:#fee2e2; border:none; cursor:pointer; color:#ef4444; width:30px; height:30px; border-radius:0.4rem; display:flex; align-items:center; justify-content:center;">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Lost Pets Near You Section -->
                        <div id="lost-pets-section" class="card lost-pets-card" 
                            style="background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 2rem; border-left: 4px solid #ef4444;">
                            <div class="card-header"
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                <div class="icon-title" style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="icon-red"
                                        style="width: 40px; height: 40px; background: #fee2e2; color: #ef4444; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-search-location"></i>
                                    </div>
                                    <h4 style="font-size: 1rem;">Lost Pets Near You</h4>
                                </div>
                                <button onclick="openReportStrayModal()" 
                                    style="background:#f3f4f6; border:none; padding:0.4rem 0.8rem; border-radius:0.5rem; font-size:0.7rem; font-weight:700; cursor:pointer; color:#4b5563;">
                                    <i class="fa-solid fa-plus"></i> Report Found Pet
                                </button>
                            </div>

                            <?php if (empty($nearbyLostPets) && empty($nearbyStrays)): ?>
                                    <div style="text-align: center; padding: 2rem 0; color: #9ca3af;">
                                        <i class="fa-solid fa-shield-cat"
                                            style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                        <p style="font-size: 0.875rem;">No lost pet reports in your area. Everything looks safe!</p>
                                    </div>
                            <?php else: ?>
                                    <div class="nearby-lost-list">
                                        <?php foreach ($nearbyLostPets as $lost): ?>
                                                <div class="lost-item"
                                                    style="display:flex; align-items:center; gap:1rem; padding: 1rem; border: 1px solid #fecaca; border-radius: 0.75rem; margin-bottom: 0.75rem; background: #fffcfc;">
                                                    <img src="<?php echo htmlspecialchars($lost['pet_image']); ?>" 
                                                        style="width: 50px; height: 50px; border-radius: 0.5rem; object-fit: cover;">
                                            
                                                    <div style="flex:1;">
                                                        <h5 style="margin:0; font-size:0.95rem; color:#991b1b;"><?php echo htmlspecialchars($lost['pet_name']); ?> (<?php echo htmlspecialchars($lost['pet_breed']); ?>)</h5>
                                                        <div style="font-size:0.8rem; color:#b91c1c; margin-top:0.25rem;">
                                                            <i class="fa-solid fa-location-dot" style="margin-right:4px;"></i> 
                                                            Lost: <?php echo htmlspecialchars($lost['last_seen_location']); ?>
                                                        </div>
                                                    </div>

                                                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.5rem;">
                                                        <?php if ($lost['user_id'] == $user_id): ?>
                                                                <span style="font-size: 0.6rem; background: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-weight: 800;">YOUR REPORT</span>
                                                        <?php endif; ?>
                                                        <button onclick="openFoundReportModal(<?php echo $lost['id']; ?>, '<?php echo htmlspecialchars($lost['pet_name']); ?>')" 
                                                            style="background:#ef4444; color:white; border:none; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                                                            Report Sighting
                                                        </button>
                                                    </div>
                                                </div>
                                        <?php endforeach; ?>

                                        <?php foreach ($nearbyStrays as $stray): ?>
                                                <div class="lost-item"
                                                    style="display:flex; align-items:center; gap:1rem; padding: 1rem; border: 1px solid #dcfce7; border-radius: 0.75rem; margin-bottom: 0.75rem; background: #f0fdf4;">
                                                    <img src="<?php echo htmlspecialchars($stray['pet_image']); ?>" 
                                                        style="width: 50px; height: 50px; border-radius: 0.5rem; object-fit: cover;">
                                            
                                                    <div style="flex:1;">
                                                        <h5 style="margin:0; font-size:0.95rem; color:#166534;">Found: <?php echo htmlspecialchars($stray['pet_breed']); ?></h5>
                                                        <div style="font-size:0.8rem; color:#15803d; margin-top:0.25rem;">
                                                            <i class="fa-solid fa-location-dot" style="margin-right:4px;"></i> 
                                                            At: <?php echo htmlspecialchars($stray['found_location']); ?>
                                                        </div>
                                                    </div>
                                                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.5rem;">
                                                        <?php if ($stray['reporter_id'] == $user_id): ?>
                                                                <span style="font-size: 0.6rem; background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; font-weight: 800;">YOUR REPORT</span>
                                                        <?php endif; ?>
                                                        <span style="font-size: 0.65rem; background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-weight: 700;">STRAY REPORT</span>
                                                    </div>
                                                </div>
                                        <?php endforeach; ?>
                                    </div>
<?php endif; ?>
                        </div>
                    </div>

                    <!-- Column 2: Health Status -->
                    <div class="grid-col-right">
                        <!-- Pet Owner Profile Card -->
                        <div class="card profile-card"
                            style="background: white; padding: 1.5rem; border-radius: 1.25rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 2rem; text-align: center; border: 1px solid #f3f4f6;">
                            <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=3b82f6&color=fff&size=100"
                                    style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid #f3f4f6;">
                                <div
                                    style="position: absolute; bottom: 5px; right: 5px; width: 20px; height: 20px; background: #10b981; border: 3px solid white; border-radius: 50%;">
                                </div>
                            </div>
                            <h3 style="font-family: 'Outfit'; font-size: 1.25rem; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($user_name); ?>
                            </h3>
                            <p style="color: #6b7280; font-size: 0.85rem; margin-bottom: 1rem;">Premium Member</p>
                            <div
                                style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #9ca3af; font-size: 0.8rem;">
                                <i class="fa-solid fa-location-dot"></i> San Francisco, CA
                            </div>
                            <a href="profile.php" class="btn btn-outline"
                                style="width: 100%; margin-top: 1.5rem; padding: 0.6rem; border-radius: 0.75rem; font-size: 0.85rem; text-decoration: none; display: block; border: 1px solid #e5e7eb; color: #374151; transition: 0.2s;">Edit
                                Profile</a>
                        </div>

                        <div class="card health-status-card"
                            style="background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div class="card-header centered-header" style="text-align: center; margin-bottom: 1.5rem;">
                                <div class="heart-icon-bg"
                                    style="width: 50px; height: 50px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fa-solid fa-heart"></i>
                                </div>
                                <h4>Health Status</h4>
                            </div>

                            <div class="health-metrics">
                                <?php if (empty($upcomingHealth)): ?>
                                        <div style="text-align: center; color: #10b981; padding: 1rem;">
                                            <i class="fa-solid fa-check-circle"
                                                style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                            <p style="font-size: 0.9rem;">All clear! No pending health alerts.</p>
                                        </div>
                                <?php else: ?>
                                        <?php foreach ($upcomingHealth as $h):
                                            $dueDate = new DateTime($h['due_at']);
                                            $today = new DateTime();
                                            $diff = $today->diff($dueDate);
                                            $daysLeft = $diff->days * ($diff->invert ? -1 : 1);

                                            $color = '#10b981'; // green
                                            $width = '100%';
                                            $dueText = "Due in $daysLeft days";

                                            if ($daysLeft < 0) {
                                                $color = '#ef4444'; // red (overdue)
                                                $dueText = "Overdue by " . abs($daysLeft) . " days";
                                                $width = '100%';
                                            } elseif ($daysLeft <= 3) {
                                                $color = '#f59e0b'; // orange (urgent)
                                                $width = '90%';
                                            } elseif ($daysLeft <= 7) {
                                                $color = '#3b82f6'; // blue
                                                $width = '75%';
                                            } else {
                                                $width = '50%';
                                            }
                                            ?>
                                                <div class="metric-item" style="margin-bottom: 1.5rem;">
                                                    <div class="flex justify-between text-sm mb-1"
                                                        style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                                        <span><?php echo htmlspecialchars($h['pet_name'] . ' - ' . $h['title']); ?></span>
                                                        <!-- Assuming title exists, or use message -->
                                                        <span
                                                            style="color: <?php echo $color; ?>; font-weight: 700;"><?php echo $dueText; ?></span>
                                                    </div>
                                                    <div class="progress-bar-bg"
                                                        style="background: #f3f4f6; height: 8px; border-radius: 4px;">
                                                        <div class="progress-bar"
                                                            style="width: <?php echo $width; ?>; background: <?php echo $color; ?>; height: 100%; border-radius: 4px;">
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Daily Tasks Card -->
                        <div class="card daily-tasks-card"
                            style="background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 2rem;">
                            <div class="card-header centered-header" style="text-align: center; margin-bottom: 1.5rem;">
                                <div class="icon-bg"
                                    style="width: 50px; height: 50px; background: #eff6ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <h4 style="font-family: 'Outfit'; font-size: 1.1rem; margin-bottom: 0.25rem;">Daily Routine</h4>
                                <p style="color: #6b7280; font-size: 0.85rem;">Keep on track today</p>
                            </div>

                            <div class="tasks-list">
                                <?php if (empty($dailyTasks)): ?>
                                        <div style="text-align: center; color: #9ca3af; padding: 1rem;">
                                            <p style="font-size: 0.875rem;">No tasks for today.</p>
                                            <a href="health-records.php" style="font-size: 0.8rem; color: #3b82f6;">+ Add Task</a>
                                        </div>
                                <?php else: ?>
                                        <?php foreach ($dailyTasks as $task): ?>
                                                <div class="task-item" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.75rem;">
                                                    <div class="task-check <?php echo $task['is_done'] ? 'done' : ''; ?>" style="width: 20px; height: 20px; border-radius: 6px; border: 2px solid #e5e7eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; <?php echo $task['is_done'] ? 'background:#3b82f6; border-color:#3b82f6;' : ''; ?>">
                                                        <?php if ($task['is_done']): ?><i class="fa-solid fa-check" style="font-size:10px; color:white;"></i><?php endif; ?>
                                                    </div>
                                                    <div class="task-info" style="flex: 1;">
                                                        <h4 style="font-size: 0.9rem; margin-bottom: 0.1rem; color: #1e293b;"><?php echo htmlspecialchars($task['task_name']); ?></h4>
                                                        <p style="font-size: 0.75rem; color: #64748b; margin:0;" class="task-time-display"><?php echo htmlspecialchars($task['task_time']); ?></p>
                                                    </div>
                                                </div>
                                        <?php endforeach; ?>
                                        <a href="health-records.php" style="display: block; text-align: center; font-size: 0.8rem; color: #3b82f6; font-weight: 600; margin-top: 1rem;">Manage Tasks</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mark as Lost Modal -->
    <div id="lostModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:1.5rem; width:100%; max-width:450px; position:relative;">
            <button onclick="closeLostModal()" style="position:absolute; top:1.5rem; right:1.5rem; border:none; background:none; font-size:1.25rem; cursor:pointer; color:#9ca3af;"><i class="fa-solid fa-times"></i></button>
            <h2 style="font-family:'Outfit'; margin-bottom:0.5rem; color:#ef4444;">Mark <span id="lostPetName">Pet</span> as Lost</h2>
            <p style="color:#6b7280; font-size:0.9rem; margin-bottom:1.5rem;">Provide details to alert nearby users.</p>
            
            <form id="lostPetForm" onsubmit="submitLostPet(event)">
                <input type="hidden" id="lostPetId" name="pet_id">
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Last Seen Location (Area/City)</label>
                    <input type="text" name="last_seen_location" required style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="e.g. Central Park, New York">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Last Seen Date</label>
                    <input type="date" name="last_seen_date" required style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Description</label>
                    <textarea name="description" rows="3" style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="Any distinguishing features or collar color..."></textarea>
                </div>
                <button type="submit" style="width:100%; padding:1rem; background:#ef4444; color:white; border:none; border-radius:0.75rem; font-weight:700; cursor:pointer;">Broadcast Alert</button>
            </form>
        </div>
    </div>

    <!-- Found Report Modal -->
    <div id="foundReportModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:1.5rem; width:100%; max-width:450px; position:relative;">
            <button onclick="closeFoundReportModal()" style="position:absolute; top:1.5rem; right:1.5rem; border:none; background:none; font-size:1.25rem; cursor:pointer; color:#9ca3af;"><i class="fa-solid fa-times"></i></button>
            <h2 style="font-family:'Outfit'; margin-bottom:0.5rem; color:#10b981;">Report Sighting of <span id="foundPetName">Pet</span></h2>
            <p style="color:#6b7280; font-size:0.9rem; margin-bottom:1.5rem;">Help the owner find their pet!</p>
            
            <form id="foundReportForm" onsubmit="submitFoundReport(event)">
                <input type="hidden" id="foundAlertId" name="alert_id">
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Found/Seen Location</label>
                    <input type="text" name="found_location" required style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="Where did you see the pet?">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Contact Info (Optional)</label>
                    <input type="text" name="contact_info" style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="Your phone or email">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Notes</label>
                    <textarea name="notes" rows="3" style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="Any details about its condition or behavior..."></textarea>
                </div>
                <button type="submit" style="width:100%; padding:1rem; background:#10b981; color:white; border:none; border-radius:0.75rem; font-weight:700; cursor:pointer;">Submit Report</button>
            </form>
        </div>
    </div>

    <!-- Report Stray Modal -->
    <div id="reportStrayModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:1.5rem; width:100%; max-width:450px; position:relative;">
            <button onclick="closeReportStrayModal()" style="position:absolute; top:1.5rem; right:1.5rem; border:none; background:none; font-size:1.25rem; cursor:pointer; color:#9ca3af;"><i class="fa-solid fa-times"></i></button>
            <h2 style="font-family:'Outfit'; margin-bottom:0.5rem; color:#166534;">Report a Found Pet (Stray)</h2>
            <p style="color:#6b7280; font-size:0.9rem; margin-bottom:1.5rem;">Can't find the pet in the lost list? Post it here so owners can find it.</p>
            
            <form id="reportStrayForm" onsubmit="submitStrayReport(event)">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom:1rem;">
                    <div>
                        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Animal Type</label>
                        <select name="pet_type" style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;">
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                            <option value="Bird">Bird</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Possible Breed</label>
                        <input type="text" name="pet_breed" style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="e.g. Beagle">
                    </div>
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Found at Location</label>
                    <input type="text" name="found_location" required style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="Area or Landmarks">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Your Contact (Optional)</label>
                    <input type="text" name="contact_info" style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="Phone or email">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Additional Notes</label>
                    <textarea name="description" rows="3" style="width:100%; padding:0.75rem; border:1.5px solid #e5e7eb; border-radius:0.75rem;" placeholder="Health condition, collar color, etc."></textarea>
                </div>
                <button type="submit" style="width:100%; padding:1rem; background:#166534; color:white; border:none; border-radius:0.75rem; font-weight:700; cursor:pointer;">Post Found Pet</button>
            </form>
        </div>
    </div>

    <script>
        function openLostModal(id, name) {
            document.getElementById('lostPetId').value = id;
            document.getElementById('lostPetName').innerText = name;
            document.getElementById('lostModal').style.display = 'flex';
        }

        function closeLostModal() {
            document.getElementById('lostModal').style.display = 'none';
        }

        function openFoundReportModal(alertId, name) {
            document.getElementById('foundAlertId').value = alertId;
            document.getElementById('foundPetName').innerText = name;
            document.getElementById('foundReportModal').style.display = 'flex';
        }

        function closeFoundReportModal() {
            document.getElementById('foundReportModal').style.display = 'none';
        }

        async function submitLostPet(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('api/mark_pet_lost.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }

        async function submitFoundReport(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('api/submit_found_report.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }

        function openReportStrayModal() {
            document.getElementById('reportStrayModal').style.display = 'flex';
        }

        function closeReportStrayModal() {
            document.getElementById('reportStrayModal').style.display = 'none';
        }

        async function submitStrayReport(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('api/report_stray.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }

        // Global Search Functionality
        const searchInput = document.getElementById('dashboard-search');
        if (searchInput) {
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    const query = this.value.toLowerCase().trim();
                    if (!query) return;

                    // 1. Check for page keywords
                    if (query.includes('pet') || query.includes('adopt')) {
                        window.location.href = 'adoption.php?search=' + encodeURIComponent(query);
                    } else if (query.includes('shop') || query.includes('buy') || query.includes('food')) {
                        window.location.href = 'marketplace.php?search=' + encodeURIComponent(query);
                    } else if (query.includes('health') || query.includes('record')) {
                        window.location.href = 'health-records.php';
                    } else if (query.includes('appoint') || query.includes('visit')) {
                        window.location.href = 'schedule.php';
                    } else {
                        // Default: search in marketplace
                        window.location.href = 'marketplace.php?search=' + encodeURIComponent(query);
                    }
                }
            });

            // Optional: Live filtering of on-page cards
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const cards = document.querySelectorAll('.card');
                
                cards.forEach(card => {
                    const text = card.innerText.toLowerCase();
                    if (text.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Notification Dropdown Toggle
        const notifTrigger = document.getElementById('notif-trigger');
        const notifDropdown = document.getElementById('notif-dropdown');

        if (notifTrigger && notifDropdown) {
            notifTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                if (notifDropdown.style.display === 'none') {
                    notifDropdown.style.display = 'block';
                } else {
                    notifDropdown.style.display = 'none';
                }
            });

            document.addEventListener('click', function(e) {
                if (!notifDropdown.contains(e.target) && !notifTrigger.contains(e.target)) {
                    notifDropdown.style.display = 'none';
                }
            });
        }

        // --- NEW: Alarm System ---
        setInterval(function() {
             const now = new Date();
             const currentHours = String(now.getHours()).padStart(2, '0');
             const currentMinutes = String(now.getMinutes()).padStart(2, '0');
             const currentTime = `${currentHours}:${currentMinutes}`;
             
             document.querySelectorAll('.task-time-display').forEach(tTime => {
                 const tItem = tTime.closest('.task-item');
                 const check = tItem.querySelector('.task-check');
                 if(check && !check.classList.contains('done')) {
                     const tText = tTime.innerText.trim();
                     if(tText === currentTime) {
                         const name = tItem.querySelector('h4').innerText;
                         
                         const sound = document.getElementById('alarmSound');
                         if(sound) sound.play().catch(console.error);
                         
                         if(Notification.permission === "granted") {
                             new Notification("Task Reminder! ⏰", { body: `It's time for: ${name}` });
                         }
                         alert(`⏰ ALARM: It's time for "${name}"!`);
                     }
                 }
             });
        }, 15000); // Check every 15s

        if ("Notification" in window && Notification.permission !== "granted") {
             Notification.requestPermission();
        }
    </script>
    
    <!-- Alarm Sound -->
    <audio id="alarmSound" preload="auto">
        <source src="https://assets.mixkit.co/service/sfx/preview/2869.mp3" type="audio/mpeg">
    </audio>
</body>

</html>