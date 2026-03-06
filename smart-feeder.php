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

    // 1) Update dashboard "Recent Activity" (feeding_logs)
    $stmt = $pdo->prepare("INSERT INTO feeding_logs (user_id, pet_id, quantity_grams, status, message) VALUES (?, ?, ?, 'Success', 'Manual feeding triggered from dashboard')");
    $stmt->execute([$user_id, $pet_id, $qty]);

    // 2) Send command to ESP32 (feed_commands)
    $device_id = 'esp32_1';
    $stmtCmd = $pdo->prepare("INSERT INTO feed_commands (device_id, portion_qty, status) VALUES (?, ?, 'pending')");
    $stmtCmd->execute([$device_id, $qty]);

    // 3) Update "Last fed" display immediately (feed_logs)
    $stmtFeedLog = $pdo->prepare("INSERT INTO feed_logs (device_id, `portion`) VALUES (?, ?)");
    $stmtFeedLog->execute([$device_id, $qty]);

    header("Location: smart-feeder.php?msg=manual_success");
    exit();
}

// Handle Save Schedule
if (isset($_POST['action']) && $_POST['action'] === 'save_schedule') {
    $pet_id = $_POST['pet_id'];
    $time = $_POST['feeding_time'];
    $qty = $_POST['quantity'];

    // Simplified: Weekly/Daily default to Daily for minimal version
    $stmt = $pdo->prepare("INSERT INTO smart_feeder_schedules (user_id, pet_id, feeding_time, quantity_grams, mode, frequency) VALUES (?, ?, ?, ?, 'Manual', 'Daily')");
    $stmt->execute([$user_id, $pet_id, $time, $qty]);

    header("Location: smart-feeder.php?msg=schedule_saved");
    exit();
}

// Fetch User's Pets
$petsStmt = $pdo->prepare("SELECT id, pet_name FROM user_pets WHERE user_id = ?");
$petsStmt->execute([$user_id]);
$myPets = $petsStmt->fetchAll();

// Fetch Feeding History (Last 5)
$historyStmt = $pdo->prepare("SELECT fl.*, up.pet_name FROM feeding_logs fl JOIN user_pets up ON fl.pet_id = up.id WHERE fl.user_id = ? ORDER BY fl.feeding_time DESC LIMIT 5");
$historyStmt->execute([$user_id]);
$feedingHistory = $historyStmt->fetchAll();

// Fetch Active Schedules (for alarm system)
$scheduleStmt = $pdo->prepare("SELECT s.*, up.pet_name FROM smart_feeder_schedules s JOIN user_pets up ON s.pet_id = up.id WHERE s.user_id = ? AND s.status = 'Active' ORDER BY s.feeding_time ASC");
$scheduleStmt->execute([$user_id]);
$activeSchedules = $scheduleStmt->fetchAll();

// Simulated IoT Status
$deviceStatus = "Online";
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
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        .feeder-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        @media (max-width: 1024px) {
            .feeder-grid {
                grid-template-columns: 1fr;
            }
        }

        .minimal-card {
            background: var(--card-bg);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(229, 231, 235, 0.5);
            transition: transform 0.3s ease;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 700;
            background: #ecfdf5;
            color: #10b981;
        }

        .status-badge.offline {
            background: #fef2f2;
            color: #ef4444;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 8px currentColor;
        }

        .portion-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .portion-option {
            cursor: pointer;
        }

        .portion-option input {
            display: none;
        }

        .portion-card {
            padding: 1rem;
            border-radius: 1rem;
            border: 2px solid #f1f5f9;
            text-align: center;
            transition: all 0.2s;
        }

        .portion-option input:checked+.portion-card {
            border-color: var(--primary);
            background: #f5f3ff;
            color: var(--primary);
        }

        .btn-feed {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 1.25rem;
            border-radius: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .btn-feed:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -3px rgba(79, 70, 229, 0.4);
        }

        .history-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .input-minimal {
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-family: inherit;
            outline: none;
            transition: border 0.2s;
        }

        .input-minimal:focus {
            border-color: var(--primary);
        }

        .section-title {
            font-family: 'Outfit';
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>

<body class="dashboard-page">
    <div class="dashboard-container">
        <?php include 'user-sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <button class="menu-toggle-btn" onclick="if(window.toggleUserSidebar) window.toggleUserSidebar();">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div style="flex-grow: 1;"></div>
                <div class="header-actions">
                    <a href="mypets.php" class="btn"
                        style="background: #4b5e71; color: white; padding: 0.75rem 1.75rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem;">
                        MY PETS
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="page-title" style="margin-bottom: 2rem;">
                    <h2 style="font-family: 'Outfit'; font-size: 2.2rem; font-weight: 700;">Smart Feeder</h2>
                    <p style="color: var(--text-muted); font-size: 1.1rem;">Simple, instant control for your pets.</p>
                </div>

                <div class="feeder-grid">
                    <!-- Left: Control -->
                    <div class="feeder-left">
                        <div class="minimal-card">
                            <div
                                style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="section-title" style="margin-bottom: 0.25rem;">Feeder Terminal</h3>
                                    <p style="color: var(--text-muted); font-size: 0.9rem;">Direct hardware control</p>
                                </div>
                                <div class="status-badge">
                                    <div class="dot"></div> ONLINE
                                </div>
                            </div>

                            <div
                                style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 500;">
                                <div style="color: var(--text-main);"><i class="fa-solid fa-clock"
                                        style="color: var(--primary); margin-right: 0.5rem;"></i><span
                                        id="lastFedText">Last fed: --</span></div>
                                <div style="color: var(--text-main);"><i class="fa-solid fa-bowl-food"
                                        style="color: var(--primary); margin-right: 0.5rem;"></i><span
                                        id="lastPortionText">Last portion: --</span></div>
                            </div>

                            <form action="" method="POST">
                                <input type="hidden" name="action" value="manual_feed">

                                <div style="margin-bottom: 1.5rem;">
                                    <label class="form-label">Select Pet</label>
                                    <select name="pet_id" class="input-minimal" required>
                                        <?php foreach ($myPets as $pet): ?>
                                            <option value="<?php echo $pet['id']; ?>">
                                                <?php echo htmlspecialchars($pet['pet_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <label class="form-label">Portion Size</label>
                                <div class="portion-grid">
                                    <label class="portion-option portionBtn" data-portion="30">
                                        <input type="radio" name="quantity" value="30" checked>
                                        <div class="portion-card">
                                            <div style="font-weight: 700;">Small</div>
                                            <div style="font-size: 0.8rem; opacity: 0.7;">30g</div>
                                        </div>
                                    </label>
                                    <label class="portion-option portionBtn" data-portion="60">
                                        <input type="radio" name="quantity" value="60">
                                        <div class="portion-card">
                                            <div style="font-weight: 700;">Medium</div>
                                            <div style="font-size: 0.8rem; opacity: 0.7;">60g</div>
                                        </div>
                                    </label>
                                    <label class="portion-option portionBtn" data-portion="100">
                                        <input type="radio" name="quantity" value="100">
                                        <div class="portion-card">
                                            <div style="font-weight: 700;">Large</div>
                                            <div style="font-size: 0.8rem; opacity: 0.7;">100g</div>
                                        </div>
                                    </label>
                                </div>



                                <button type="submit" class="btn-feed" id="feedBtn">
                                    <i class="fa-solid fa-bolt"></i> FEED NOW
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Schedule & History -->
                    <div class="feeder-right">
                        <!-- Simple Schedule -->
                        <div class="minimal-card" style="margin-bottom: 2rem;">
                            <h3 class="section-title"><i class="fa-regular fa-clock" style="color: var(--primary);"></i>
                                Simple Schedule</h3>
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="save_schedule">
                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                    <div>
                                        <label class="form-label">Pet</label>
                                        <select name="pet_id" class="input-minimal" required>
                                            <?php foreach ($myPets as $pet): ?>
                                                <option value="<?php echo $pet['id']; ?>">
                                                    <?php echo htmlspecialchars($pet['pet_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Time</label>
                                        <input type="time" name="feeding_time" class="input-minimal" required>
                                    </div>
                                </div>
                                <div style="margin-bottom: 1.5rem;">
                                    <label class="form-label">Portion (Grams)</label>
                                    <input type="number" id="portionInput" name="quantity" class="input-minimal"
                                        value="40" min="10" step="10">
                                </div>
                                <button type="submit" class="btn"
                                    style="width:100%; padding: 1rem; border-radius: 0.75rem; background: var(--text-main); color: white; border: none; font-weight: 600; cursor: pointer;">
                                    Save Schedule
                                </button>
                            </form>
                        </div>

                        <!-- Recent Logs -->
                        <div class="minimal-card">
                            <h3 class="section-title"><i class="fa-solid fa-list-ul" style="color: #10b981;"></i> Recent
                                Activity</h3>
                            <ul class="history-list">
                                <?php if (empty($feedingHistory)): ?>
                                    <li style="text-align: center; color: var(--text-muted); padding: 2rem;">No logs found.
                                    </li>
                                <?php else: ?>
                                    <?php foreach ($feedingHistory as $log): ?>
                                        <li class="history-item">
                                            <div>
                                                <div style="font-weight: 600; font-size: 0.95rem;">
                                                    <?php echo htmlspecialchars($log['pet_name']); ?>
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    <?php echo date('M d, g:i A', strtotime($log['feeding_time'])); ?>
                                                </div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-weight: 700; color: var(--text-main);">
                                                    <?php echo $log['quantity_grams']; ?>g
                                                </div>
                                                <div
                                                    style="font-size: 0.7rem; font-weight: 700; border-radius: 4px; padding: 2px 6px; background: #dcfce7; color: #166534; display: inline-block;">
                                                    SUCCESS</div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>


        // Alarm System Integration
        const schedules = <?php echo json_encode($activeSchedules); ?>;
        let lastTriggeredTime = null;

        function checkAlarms() {
            const now = new Date();
            const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            if (lastTriggeredTime === currentTime) return;

            schedules.forEach(schedule => {
                const scheduleTime = schedule.feeding_time.substring(0, 5);
                if (currentTime === scheduleTime) {
                    alert(`Feeding Time for ${schedule.pet_name}! (${schedule.quantity_grams}g)`);
                    lastTriggeredTime = currentTime;
                }
            });
        }
        setInterval(checkAlarms, 10000);
    </script>
    <script>
        // Portion Selection Logic
        document.querySelectorAll('.portionBtn input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const portion = this.closest('.portionBtn').getAttribute('data-portion');
                const input = document.getElementById('portionInput');
                if (input) {
                    input.value = portion;
                }
            });
        });
        // Last Feed Tracker Fetch Logic
        function fetchLastFeed() {
            fetch('api/get_last_feed.php?device_id=esp32_1')
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        if (data.fed_at) {
                            document.getElementById('lastFedText').innerText = `Last fed: ${data.fed_at}`;
                            document.getElementById('lastPortionText').innerText = `Last portion: ${data.portion}g`;
                        } else {
                            document.getElementById('lastFedText').innerText = 'Last fed: Not yet';
                            document.getElementById('lastPortionText').innerText = 'Last portion: --';
                        }
                    }
                })
                .catch(err => console.error('Error fetching last feed:', err));
        }

        // Fetch on load, then every 5 seconds
        fetchLastFeed();
        setInterval(fetchLastFeed, 5000);
    </script>
</body>

</html>