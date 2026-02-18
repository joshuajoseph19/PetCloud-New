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

// --- AUTO-FIX: Create Table If Missing ---
try {
    $pdo->query("SELECT 1 FROM smart_feeder_schedules LIMIT 1");
} catch (PDOException $e) {
    include 'setup_smart_feeder_db.php';
}

// Handle Manual Feeding Simulation
if (isset($_POST['action']) && $_POST['action'] === 'manual_feed') {
    $pet_id = $_POST['pet_id'];
    $qty = $_POST['quantity'];

    $stmt = $pdo->prepare("INSERT INTO feeding_logs (user_id, pet_id, quantity_grams, status, message) VALUES (?, ?, ?, 'Success', 'Manual feeding triggered from dashboard')");
    $stmt->execute([$user_id, $pet_id, $qty]);

    header("Location: smart-feeder.php?msg=manual_success");
    exit();
}

// Handle Save Schedule
if (isset($_POST['action']) && $_POST['action'] === 'save_schedule') {
    $pet_id = $_POST['pet_id'];
    $time = $_POST['feeding_time'];
    $qty = $_POST['quantity'];
    $mode = $_POST['mode'];
    $frequency = $_POST['frequency'] ?? 'Daily'; // Daily Default

    $stmt = $pdo->prepare("INSERT INTO smart_feeder_schedules (user_id, pet_id, feeding_time, quantity_grams, mode, frequency) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $pet_id, $time, $qty, $mode, $frequency]);

    header("Location: smart-feeder.php?msg=schedule_saved");
    exit();
}

// Fetch User's Pets
$petsStmt = $pdo->prepare("SELECT id, pet_name FROM user_pets WHERE user_id = ?");
$petsStmt->execute([$user_id]);
$myPets = $petsStmt->fetchAll();

// Fetch Feeding History
$historyStmt = $pdo->prepare("SELECT fl.*, up.pet_name FROM feeding_logs fl JOIN user_pets up ON fl.pet_id = up.id WHERE fl.user_id = ? ORDER BY fl.feeding_time DESC LIMIT 10");
$historyStmt->execute([$user_id]);
$feedingHistory = $historyStmt->fetchAll();

// Fetch Active Schedules
$scheduleStmt = $pdo->prepare("SELECT s.*, up.pet_name FROM smart_feeder_schedules s JOIN user_pets up ON s.pet_id = up.id WHERE s.user_id = ? AND s.status = 'Active' ORDER BY s.feeding_time ASC");
$scheduleStmt->execute([$user_id]);
$activeSchedules = $scheduleStmt->fetchAll();

// Simulated IoT Status
$deviceStatus = "Connected";
$wifiStrength = "Strong";
$lastFeed = !empty($feedingHistory) ? date('g:i A', strtotime($feedingHistory[0]['feeding_time'])) : "No data";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Feeder - PetCloud</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .feeder-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding-bottom: 3rem;
        }

        .status-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .indicator-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .btn-feed-now {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-feed-now:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }

        .custom-form-group {
            margin-bottom: 1.5rem;
        }

        .custom-form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: #4b5563;
        }

        .custom-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
            font-family: inherit;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            text-align: left;
            padding: 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #94a3b8;
            border-bottom: 1px solid #f1f5f9;
        }

        .history-table td {
            padding: 1rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-pill {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
        }

        .status-failed {
            background: #fef2f2;
            color: #991b1b;
        }

        .notification-panel {
            background: #f8fafc;
            border-radius: 1rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .notif-item {
            display: flex;
            gap: 1rem;
            padding: 0.75rem;
            background: white;
            border-radius: 0.75rem;
            border-left: 4px solid #4f46e5;
            font-size: 0.85rem;
        }
    </style>
</head>

<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'user-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="dashboard-search" placeholder="Search feeder logs or schedules...">
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
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
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
                                        <i class="fa-solid fa-bone"></i>
                                    </div>
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
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>
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
                        style="background: #4b5e71; color: white; padding: 0.75rem 1.75rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem;">
                        ADD A PET
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="page-title" style="margin-bottom: 2rem;">
                    <h2 style="font-family: 'Outfit'; font-size: 2rem;">Smart Feeder Control</h2>
                    <p style="color: #64748b;">Manage your pet's dietary needs through IoT cloud control.</p>
                </div>

                <div class="feeder-container">
                    <!-- Column 1: Status & Manual Control -->
                    <div class="feeder-col">
                        <div class="status-card">
                            <div class="status-header">
                                <h3 style="font-family: 'Outfit'; font-size: 1.25rem;">Device Status</h3>
                                <div class="status-indicator">
                                    <div class="indicator-dot" style="background: #10b981;"></div>
                                    <span style="color: #10b981;">ONLINE</span>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div style="background: #f8fafc; padding: 1rem; border-radius: 1rem;">
                                    <span
                                        style="font-size: 0.75rem; color: #64748b; display: block; margin-bottom: 0.25rem;">Wi-Fi
                                        Strength</span>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                                        <i class="fa-solid fa-wifi" style="color: #3b82f6;"></i> 92% (Strong)
                                    </div>
                                </div>
                                <div style="background: #f8fafc; padding: 1rem; border-radius: 1rem;">
                                    <span
                                        style="font-size: 0.75rem; color: #64748b; display: block; margin-bottom: 0.25rem;">Last
                                        Feed</span>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                                        <i class="fa-solid fa-clock-rotate-left" style="color: #4f46e5;"></i>
                                        <?php echo $lastFeed; ?>
                                    </div>
                                </div>
                            </div>

                            <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 0.5rem 0;">

                            <form action="" method="POST">
                                <input type="hidden" name="action" value="manual_feed">
                                <h4 style="font-size: 0.9rem; margin-bottom: 1rem;">Instant Manual Feeding</h4>
                                <div class="custom-form-group">
                                    <label>Target Pet</label>
                                    <select name="pet_id" class="custom-input" required>
                                        <?php foreach ($myPets as $pet): ?>
                                            <option value="<?php echo $pet['id']; ?>">
                                                <?php echo htmlspecialchars($pet['pet_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="custom-form-group">
                                    <label>Portion Size (Grams)</label>
                                    <input type="number" name="quantity" class="custom-input" value="50" min="10"
                                        step="10">
                                </div>
                                <button type="submit" class="btn-feed-now w-full">
                                    <i class="fa-solid fa-bolt"></i> Trigger Feed Now
                                </button>
                            </form>
                        </div>

                        <div class="status-card" style="margin-top: 2rem;">
                            <h3 style="font-family: 'Outfit'; font-size: 1.1rem; margin-bottom: 1rem;">Real-time
                                Notifications</h3>
                            <div class="notification-panel">
                                <div class="notif-item">
                                    <i class="fa-solid fa-circle-check" style="color: #10b981; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-weight: 700;">Feeding Successful</div>
                                        <div style="color: #64748b; font-size: 0.75rem;">15 mins ago • Leo (40g)</div>
                                    </div>
                                </div>
                                <div class="notif-item" style="border-left-color: #f59e0b;">
                                    <i class="fa-solid fa-triangle-exclamation"
                                        style="color: #f59e0b; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-weight: 700;">Low Food Alert</div>
                                        <div style="color: #64748b; font-size: 0.75rem;">2 hours ago • Tank at 15%</div>
                                    </div>
                                </div>
                                <div class="notif-item" style="border-left-color: #ef4444;">
                                    <i class="fa-solid fa-cloud-slash" style="color: #ef4444; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-weight: 700;">Device Offline</div>
                                        <div style="color: #64748b; font-size: 0.75rem;">Yesterday • Unexpected
                                            disconnect</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Scheduling & History -->
                    <div class="feeder-col">
                        <div class="status-card" style="height: fit-content;">
                            <h3 style="font-family: 'Outfit'; font-size: 1.25rem; margin-bottom: 1.5rem;">Configure New
                                Schedule</h3>
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="save_schedule">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="custom-form-group">
                                        <label>Select Pet</label>
                                        <select name="pet_id" class="custom-input" required>
                                            <?php foreach ($myPets as $pet): ?>
                                                <option value="<?php echo $pet['id']; ?>">
                                                    <?php echo htmlspecialchars($pet['pet_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="custom-form-group">
                                        <label>Feeding Time</label>
                                        <input type="time" name="feeding_time" class="custom-input" required>
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="custom-form-group">
                                        <label>Quantity (g)</label>
                                        <input type="number" name="quantity" class="custom-input" value="30" min="5"
                                            step="5">
                                    </div>
                                    <div class="custom-form-group">
                                        <label>Mode</label>
                                        <select name="mode" class="custom-input">
                                            <option value="Automatic">Automatic (Smart)</option>
                                            <option value="Manual">One-time Schedule</option>
                                        </select>
                                    </div>
                                    <div class="custom-form-group">
                                        <label>Frequency (Reminder)</label>
                                        <select name="frequency" class="custom-input">
                                            <option value="Daily">Daily</option>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Monthly">Monthly</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn"
                                    style="width: 100%; background: #10b981; color: white; padding: 1rem; border-radius: 1rem; font-weight: 700; border: none;">
                                    Save Smart Schedule
                                </button>
                            </form>
                        </div>

                        <div class="status-card" style="margin-top: 2rem;">
                            <h3 style="font-family: 'Outfit'; font-size: 1.1rem; margin-bottom: 1.5rem;">Feeding History
                                (Logs)</h3>
                            <div style="overflow-x: auto;">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Pet</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($feedingHistory)): ?>
                                            <tr>
                                                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                                    No logs found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($feedingHistory as $log): ?>
                                                <tr>
                                                    <td>
                                                        <span style="display: block; font-weight: 600;">
                                                            <?php echo date('M d, Y', strtotime($log['feeding_time'])); ?>
                                                        </span>
                                                        <span style="font-size: 0.75rem; color: #64748b;">
                                                            <?php echo date('g:i A', strtotime($log['feeding_time'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($log['pet_name']); ?>
                                                    </td>
                                                    <td style="font-weight: 700;">
                                                        <?php echo $log['quantity_grams']; ?>g
                                                    </td>
                                                    <td><span
                                                            class="status-pill status-<?php echo strtolower($log['status']); ?>">
                                                            <?php echo $log['status']; ?>
                                                        </span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Notification Dropdown Toggle
        const notifTrigger = document.getElementById('notif-trigger');
        const notifDropdown = document.getElementById('notif-dropdown');

        if (notifTrigger && notifDropdown) {
            notifTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                if (notifDropdown.style.display === 'none') {
                    notifDropdown.style.display = 'block';
                } else {
                    notifDropdown.style.display = 'none';
                }
            });

            document.addEventListener('click', function (e) {
                if (!notifDropdown.contains(e.target) && !notifTrigger.contains(e.target)) {
                    notifDropdown.style.display = 'none';
                }
            });
        }

        // --- DAILY ALARM SYSTEM ---

        // 1. Get Schedules from PHP
        const schedules = <?php echo json_encode($activeSchedules); ?>;

        // State to prevent double firing in the same minute
        let lastTriggeredTime = null;

        // 2. Audio Context for Alarm Sound
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

        // Unlock AudioContext on first user interaction
        document.addEventListener('click', function () {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }, { once: true });

        function playAlarmSound() {
            // Create oscillator for a "digital watch" beep
            const osc = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            osc.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            osc.type = 'square';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
            osc.frequency.setValueAtTime(1760, audioCtx.currentTime + 0.1); // A6

            gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);

            osc.start();
            osc.stop(audioCtx.currentTime + 0.5);
        }

        function triggerVisualAlarm(schedule) {
            const modal = document.getElementById('alarmModal');

            if (modal) {
                // Modal exists, append pet info
                const list = document.getElementById('alarmPetList');
                if (list) {
                    // Check if pet is already in list to avoid duplicates
                    if (!list.innerHTML.includes(schedule.pet_name)) {
                        const item = document.createElement('div');
                        item.style.cssText = "background: #f1f5f9; padding: 0.5rem; border-radius: 0.5rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;";
                        item.innerHTML = `<strong>${schedule.pet_name}</strong> <span>${schedule.quantity_grams}g</span>`;
                        list.appendChild(item);

                        // Re-play sound for attention
                        playAlarmSound();
                    }
                }
                return;
            }

            // Create New Modal
            const newModal = document.createElement('div');
            newModal.id = 'alarmModal';
            newModal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.8); z-index: 10000;
                display: flex; align-items: center; justify-content: center;
                backdrop-filter: blur(5px);
                animation: fadeIn 0.3s ease-out;
            `;

            newModal.innerHTML = `
                <div style="background: white; padding: 2.5rem; border-radius: 1.5rem; text-align: center; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); animation: scaleUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                    <div style="width: 80px; height: 80px; background: #dbf4ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
                        <i class="fa-solid fa-bell fa-shake"></i>
                    </div>
                    <h2 style="font-family: 'Outfit'; margin-bottom: 0.5rem; color: #1e293b; font-size: 1.75rem;">It's Feeding Time!</h2>
                    <div id="alarmPetList" style="text-align: left; margin-bottom: 2rem; max-height: 200px; overflow-y: auto;">
                        <div style="background: #f1f5f9; padding: 0.5rem; border-radius: 0.5rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <strong>${schedule.pet_name}</strong> <span>${schedule.quantity_grams}g</span>
                        </div>
                    </div>
                    <button onclick="dismissAlarm()" style="background: #3b82f6; color: white; border: none; padding: 1rem 2rem; border-radius: 1rem; font-size: 1rem; font-weight: 700; cursor: pointer; width: 100%; transition: transform 0.2s;">
                        <i class="fa-solid fa-check"></i> Dismiss All
                    </button>
                    <p style="margin-top: 1rem; font-size: 0.8rem; color: #94a3b8;">Schedules repeat automatically.</p>
                </div>
            `;

            document.body.appendChild(newModal);

            // Play sound loop 3 times
            let count = 0;
            const interval = setInterval(() => {
                playAlarmSound();
                count++;
                if (count >= 3) clearInterval(interval);
            }, 800);
        }

        window.dismissAlarm = function () {
            const modal = document.getElementById('alarmModal');
            if (modal) {
                modal.remove();
                // Resume Audio Context if suspended (browser policy)
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            }
        };

        // 3. Polling Function
        function checkAlarms() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hours}:${minutes}`;

            // Prevent checking multiple times in the same minute if already triggered
            if (lastTriggeredTime === currentTime) return;

            schedules.forEach(schedule => {
                // Parse Schedule Time (HH:MM)
                const scheduleTime = schedule.feeding_time.substring(0, 5);

                // 1. Time Check
                if (currentTime !== scheduleTime) return;

                // 2. Frequency Check
                let shouldTrigger = false;
                const freq = schedule.frequency || 'Daily'; // Default to Daily

                if (freq === 'Daily') {
                    shouldTrigger = true;
                } else if (freq === 'Weekly') {
                    // Check if today is the same day of week as created_at
                    const createdDate = new Date(schedule.created_at.replace(" ", "T"));
                    if (now.getDay() === createdDate.getDay()) {
                        shouldTrigger = true;
                    }
                } else if (freq === 'Monthly') {
                    // Check if today is the same date as created_at
                    const createdDate = new Date(schedule.created_at.replace(" ", "T"));
                    if (now.getDate() === createdDate.getDate()) {
                        shouldTrigger = true;
                    }
                }

                if (shouldTrigger) {
                    console.log(`ALARM TRIGGERED (${freq}) for ` + schedule.pet_name);
                    triggerVisualAlarm(schedule);
                    lastTriggeredTime = currentTime;
                }
            });
        }

        // Check every second to be precise, but logic handles minute-debounce
        setInterval(checkAlarms, 1000);

        // Animation Styles
        const styleSheet = document.createElement("style");
        styleSheet.textContent = `
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes scaleUp { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
            .fa-shake { animation: shake 1s infinite; }
            @keyframes shake {
                0% { transform: rotate(0deg); }
                25% { transform: rotate(15deg); }
                50% { transform: rotate(0deg); }
                75% { transform: rotate(-15deg); }
                100% { transform: rotate(0deg); }
            }
        `;
        document.head.appendChild(styleSheet);


    </script>
</body>

</html>