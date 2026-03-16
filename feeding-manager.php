<?php
session_start();
require_once 'db_connect.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Pet Lover';
$message = "";
$error = "";

// --- Helper Functions ---
function getAgeCategory($ageStr)
{
    // Simple parser for age string like "2 years", "5 months"
    $ageStr = strtolower($ageStr);
    if (strpos($ageStr, 'month') !== false) {
        return 'Puppy/Kitten';
    }
    $years = (int) $ageStr;
    if ($years < 1)
        return 'Puppy/Kitten';
    if ($years >= 7)
        return 'Senior';
    return 'Adult';
}

function getDietSuggestion($petType, $ageCategory)
{
    $petType = strtolower($petType);
    if (strpos($petType, 'dog') !== false) {
        if ($ageCategory == 'Puppy/Kitten')
            return "High-protein puppy formula (3-4 meals/day). Growth support.";
        if ($ageCategory == 'Senior')
            return "Low-calorie, joint-support senior blend (2 meals/day).";
        return "Balanced adult maintenance diet (2 meals/day).";
    } elseif (strpos($petType, 'cat') !== false) {
        if ($ageCategory == 'Puppy/Kitten')
            return "Kitten formula rich in taurine & fats.";
        return "Complete cat food (wet/dry mix recommended).";
    }
    return "Standard balanced diet for " . ucfirst($petType) . ".";
}

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_schedule'])) {
        $pet_id = $_POST['pet_id'];
        $meal_name = $_POST['meal_name'];
        $food_desc = $_POST['food_desc'];
        $time = $_POST['time'];
        $portion = $_POST['portion'];
        $unit = $_POST['unit'];
        $days = isset($_POST['days']) ? json_encode($_POST['days']) : '[]';
        $diet_type = $_POST['diet_type'];

        if (empty($meal_name) || empty($time) || empty($portion) || empty($food_desc)) {
            $error = "All fields are required.";
        } elseif ($portion <= 0) {
            $error = "Portion size must be greater than zero.";
        } elseif ($days == '[]') {
            $error = "Please select at least one day.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO feeding_schedules (user_id, pet_id, meal_name, food_description, feeding_time, portion_size, portion_unit, days_of_week, diet_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $pet_id, $meal_name, $food_desc, $time, $portion, $unit, $days, $diet_type])) {
                $message = "Feeding routine added successfully!";
            } else {
                $error = "Failed to add routine.";
            }
        }
    } elseif (isset($_POST['delete_schedule'])) {
        $id = $_POST['schedule_id'];
        $stmt = $pdo->prepare("DELETE FROM feeding_schedules WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $message = "Routine removed.";
    }
}

// --- Fetch Data ---
// 1. Pets
$petsStmt = $pdo->prepare("SELECT * FROM user_pets WHERE user_id = ?");
$petsStmt->execute([$user_id]);
$pets = $petsStmt->fetchAll(PDO::FETCH_ASSOC);

// Selected Pet Context
$selected_pet_id = isset($_GET['pet_id']) ? $_GET['pet_id'] : (isset($pets[0]['id']) ? $pets[0]['id'] : null);
$selected_pet = null;
if ($selected_pet_id) {
    foreach ($pets as $p) {
        if ($p['id'] == $selected_pet_id) {
            $selected_pet = $p;
            break;
        }
    }
}

// 2. Schedules (Filtered by Pet)
$schedules = [];
if ($selected_pet_id) {
    $schedStmt = $pdo->prepare("SELECT * FROM feeding_schedules WHERE user_id = ? AND pet_id = ? ORDER BY feeding_time ASC");
    $schedStmt->execute([$user_id, $selected_pet_id]);
    $schedules = $schedStmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3. Suggestions
$suggestion = "";
$ageCategory = "Adult";
if ($selected_pet) {
    $ageCategory = getAgeCategory($selected_pet['pet_age'] ?? '2 years'); // Default to adult if age missing
    $suggestion = getDietSuggestion($selected_pet['pet_type'] ?? 'Dog', $ageCategory);
}

// 4. Fetch Today's History (for adherence status)
$historyStmt = $pdo->prepare("SELECT schedule_id FROM feeding_history WHERE user_id = ? AND pet_id = ? AND DATE(fed_at) = CURDATE()");
$historyStmt->execute([$user_id, $selected_pet_id]);
$fed_schedule_ids = $historyStmt->fetchAll(PDO::FETCH_COLUMN);

// Handle Mark as Fed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_fed'])) {
    $sched_id = $_POST['schedule_id'];
    // Check if not already fed
    if (!in_array($sched_id, $fed_schedule_ids)) {
        $stmt = $pdo->prepare("INSERT INTO feeding_history (schedule_id, pet_id, user_id, status) VALUES (?, ?, ?, 'completed')");
        $stmt->execute([$sched_id, $selected_pet_id, $user_id]);
        $message = "Meal marked as fed!";
        // Refresh page to show status
        header("Location: feeding-manager.php?pet_id=" . $selected_pet_id);
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feeding Manager - PetCloud</title>
    <!-- Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Base Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Embedded Styles for Feeding Page */
        :root {
            --primary: #10b981;
            /* Green theme for food/health */
            --primary-light: #d1fae5;
            --primary-dark: #047857;
        }

        .feeding-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .pet-selector select {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            background: white;
            min-width: 200px;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
        }

        @media (max-width: 1024px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #f3f4f6;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }

        .week-days-selector {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .day-checkbox {
            display: none;
        }

        .day-label {
            padding: 0.4rem 0.6rem;
            background: #f3f4f6;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            cursor: pointer;
            border: 1px solid transparent;
            transition: 0.2s;
        }

        .day-checkbox:checked+.day-label {
            background: var(--primary-light);
            color: var(--primary-dark);
            border-color: var(--primary);
            font-weight: 600;
        }

        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        /* Timetable Grid */
        .timetable {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .day-column {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 0.5rem;
            min-height: 200px;
        }

        .day-header {
            text-align: center;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            color: #6b7280;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .meal-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-left: 3px solid var(--primary);
            padding: 0.5rem;
            border-radius: 0.4rem;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
            position: relative;
            transition: transform 0.2s;
        }

        .meal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .meal-time {
            font-weight: 700;
            color: #1f2937;
            display: block;
        }

        .meal-name {
            color: #4b5563;
        }

        .meal-meta {
            font-size: 0.7rem;
            color: #9ca3af;
            margin-top: 0.2rem;
        }

        .delete-meal-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .meal-card:hover .delete-meal-btn {
            opacity: 1;
        }

        .today-card {
            border-left: 3px solid #3b82f6;
            /* Blue highlight for today */
            background: #eff6ff;
        }

        .fed-completed {
            opacity: 0.6;
            background: #f0fdf4;
            /* Green tint */
            border-left-color: #10b981;
        }

        /* Suggestions Box */
        .suggestion-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .suggestion-text {
            font-size: 0.9rem;
            color: #1e40af;
            line-height: 1.5;
        }

        .tag-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: white;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #3b82f6;
            margin-bottom: 0.5rem;
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
                <button class="menu-toggle-btn" onclick="if(window.toggleUserSidebar) window.toggleUserSidebar();">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </header>
            <div class="content-wrapper">
                <div class="feeding-container">
                    <!-- Header -->
                    <div class="page-header">
                        <div>
                            <h1
                                style="font-family: 'Outfit'; font-size: 1.8rem; color: #111827; margin-bottom: 0.5rem;">
                                Feeding Schedule</h1>
                            <p style="color: #6b7280;">Manage your pet's daily meals and nutrition.</p>
                        </div>

                        <div class="pet-selector">
                            <form method="GET" id="petForm">
                                <select name="pet_id" onchange="document.getElementById('petForm').submit()">
                                    <?php if (empty($pets)): ?>
                                        <option value="">No pets added</option>
                                    <?php else: ?>
                                        <?php foreach ($pets as $p): ?>
                                            <option value="<?php echo $p['id']; ?>" <?php echo ($selected_pet_id == $p['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($p['pet_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </form>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div
                            style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.5rem; margin-bottom:1.5rem;">
                            <i class="fa-solid fa-check-circle"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div
                            style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:0.5rem; margin-bottom:1.5rem;">
                            <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($selected_pet): ?>
                        <div class="grid-layout">
                            <!-- Left Column: Add Schedule Form -->
                            <div class="left-col">
                                <!-- Suggestion Card -->
                                <div class="suggestion-box">
                                    <span class="tag-badge">AI SUGGESTION</span>
                                    <h4 style="margin:0 0 0.5rem 0; color:#1e3a8a;">Recommended for
                                        <?php echo htmlspecialchars($selected_pet['pet_name']); ?>
                                    </h4>
                                    <div class="suggestion-text">
                                        Based on age (<?php echo $ageCategory; ?>) and breed
                                        (<?php echo htmlspecialchars($selected_pet['pet_breed'] ?? 'Unknown'); ?>):<br>
                                        <strong><?php echo $suggestion; ?></strong>
                                    </div>
                                </div>

                                <!-- Add Form -->
                                <div class="card">
                                    <h3><i class="fa-solid fa-utensils"></i> Add Meal Routine</h3>
                                    <form method="POST" id="addMealForm" onsubmit="return validateForm()">
                                        <div id="js-error-msg"
                                            style="display:none; color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9rem; align-items: center; gap: 8px;">
                                            <i class="fa-solid fa-circle-exclamation"></i> <span></span>
                                        </div>
                                        <input type="hidden" name="pet_id" value="<?php echo $selected_pet_id; ?>">
                                        <input type="hidden" name="add_schedule" value="1">

                                        <div class="form-group">
                                            <label>Meal Name</label>
                                            <input type="text" name="meal_name" class="form-control"
                                                placeholder="e.g. Morning Kibble" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Feeding Time</label>
                                            <input type="time" name="time" class="form-control" required>
                                        </div>

                                        <div class="form-group" style="display:flex; gap:0.5rem;">
                                            <div style="flex:1;">
                                                <label>Portion</label>
                                                <input type="number" step="0.1" name="portion" class="form-control"
                                                    placeholder="0.0">
                                            </div>
                                            <div style="flex:1;">
                                                <label>Unit</label>
                                                <select name="unit" class="form-control">
                                                    <option value="grams">grams</option>
                                                    <option value="cups">cups</option>
                                                    <option value="items">items</option>
                                                    <option value="scoops">scoops</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Food Description</label>
                                            <input type="text" name="food_desc" class="form-control"
                                                placeholder="e.g. Chicken & Rice">
                                        </div>

                                        <div class="form-group">
                                            <label>Diet Type</label>
                                            <select name="diet_type" class="form-control">
                                                <option value="Dry Food">Dry Food</option>
                                                <option value="Wet Food">Wet Food</option>
                                                <option value="Homemade">Homemade</option>
                                                <option value="Mixed">Mixed Diet</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <div
                                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.25rem;">
                                                <label style="margin-bottom:0;">Repeat On</label>
                                                <button type="button" onclick="selectAllDays()"
                                                    style="background:none; border:none; color:#3b82f6; font-size:0.75rem; cursor:pointer; font-weight:600;">Select
                                                    All</button>
                                            </div>
                                            <div class="week-days-selector">
                                                <?php
                                                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                                foreach ($days as $d): ?>
                                                    <input type="checkbox" id="d-<?php echo $d; ?>" name="days[]"
                                                        value="<?php echo $d; ?>" class="day-checkbox" checked>
                                                    <label for="d-<?php echo $d; ?>" class="day-label"><?php echo $d; ?></label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn-submit"><i class="fa-solid fa-plus"></i> Add to
                                            Schedule</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Right Column: Weekly Timetable -->
                            <div class="right-col">
                                <div class="card" style="min-height: 600px;">
                                    <div
                                        style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                                        <h3><i class="fa-regular fa-calendar-days"></i> Weekly Timetable</h3>
                                        <button onclick="window.print()"
                                            style="background:none; border:none; cursor:pointer; color:#6b7280;"><i
                                                class="fa-solid fa-print"></i> Print</button>
                                    </div>

                                    <div class="timetable">
                                        <?php
                                        $weekMap = ['Jan' => 1, 'Feb' => 2]; // Not used, just day names
                                        $allDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

                                        foreach ($allDays as $day):
                                            ?>
                                            <div class="day-column">
                                                <div class="day-header"><?php echo strtoupper($day); ?></div>
                                                <!-- Render meals for this day -->
                                                <?php
                                                $todayDay = date('D');
                                                $shortDay = $day; // Mon, Tue...
                                                $isToday = ($shortDay == $todayDay);

                                                if (!empty($schedules)) {
                                                    foreach ($schedules as $s) {
                                                        $sDays = json_decode($s['days_of_week'] ?? '[]');
                                                        // Handle JSON decode errors or empty
                                                        if (!is_array($sDays))
                                                            $sDays = [];

                                                        if (in_array($shortDay, $sDays)) {
                                                            $isFed = in_array($s['id'], $fed_schedule_ids);

                                                            // Render Card
                                                            $cardClass = $isToday ? 'meal-card today-card' : 'meal-card';
                                                            if ($isFed && $isToday)
                                                                $cardClass .= ' fed-completed';

                                                            echo '<div class="' . $cardClass . '">';
                                                            echo '<span class="meal-time">' . date('g:i A', strtotime($s['feeding_time'])) . '</span>';
                                                            echo '<span class="meal-name">' . htmlspecialchars($s['meal_name']) . '</span>';
                                                            echo '<div class="meal-meta">' . htmlspecialchars($s['portion_size']) . ' ' . htmlspecialchars($s['portion_unit']) . '</div>';

                                                            // Actions
                                                            echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">';

                                                            // Mark as Fed Button (Only for Today)
                                                            if ($isToday) {
                                                                if ($isFed) {
                                                                    echo '<span style="font-size:0.7rem; color:#166534; font-weight:700;"><i class="fa-solid fa-check"></i> Fed</span>';
                                                                } else {
                                                                    echo '<form method="POST" style="display:inline;">';
                                                                    echo '<input type="hidden" name="schedule_id" value="' . $s['id'] . '">';
                                                                    echo '<input type="hidden" name="mark_fed" value="1">';
                                                                    echo '<button type="submit" style="background:#dcfce7; border:none; color:#166534; font-size:0.7rem; padding:0.2rem 0.5rem; border-radius:0.3rem; cursor:pointer;">Mark Fed</button>';
                                                                    echo '</form>';
                                                                }
                                                            }

                                                            // Delete Button
                                                            echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Delete this routine?\');">';
                                                            echo '<input type="hidden" name="schedule_id" value="' . $s['id'] . '">';
                                                            echo '<input type="hidden" name="delete_schedule" value="1">';
                                                            echo '<button type="submit" class="delete-meal-btn" style="position:static; margin-left:auto;"><i class="fa-solid fa-trash-can"></i></button>';
                                                            echo '</form>';

                                                            echo '</div>'; // End Actions
                                                            echo '</div>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding: 4rem;">
                            <h2>No Pets Found</h2>
                            <p>Please <a href="mypets.php" style="color:var(--primary);">add a pet</a> to manage feeding
                                schedules.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        function selectAllDays() {
            const checkboxes = document.querySelectorAll('input[name="days[]"]');
            const allCheckedPre = Array.from(checkboxes).every(cb => cb.checked);

            // If all are checked, uncheck all. Otherwise, check all.
            const newState = !allCheckedPre;
            checkboxes.forEach(cb => cb.checked = newState);

            // Update button text
            const btn = document.querySelector('button[onclick="selectAllDays()"]');
            if (btn) btn.textContent = newState ? "Deselect All" : "Select All";
        }

        function validateForm() {
            const mealName = document.querySelector('input[name="meal_name"]').value.trim();
            const time = document.querySelector('input[name="time"]').value;
            const portion = parseFloat(document.querySelector('input[name="portion"]').value);
            const foodDesc = document.querySelector('input[name="food_desc"]').value.trim();
            const days = document.querySelectorAll('input[name="days[]"]:checked');
            const errorDiv = document.getElementById('js-error-msg');
            const errorSpan = errorDiv.querySelector('span');

            let error = '';

            if (mealName.length < 2) {
                error = "Please enter a valid Meal Name (at least 2 chars).";
            } else if (!time) {
                error = "Please select a Feeding Time.";
            } else if (isNaN(portion) || portion <= 0) {
                error = "Please enter a valid Portion size greater than 0.";
            } else if (foodDesc.length < 3) {
                error = "Please describe the food (at least 3 chars).";
            } else if (days.length === 0) {
                error = "Please select at least one day to repeat.";
            }

            if (error) {
                errorSpan.textContent = error;
                errorDiv.style.display = 'flex';
                // Scroll to error
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            errorDiv.style.display = 'none';
            return true;
        }
    </script>
</body>

</html>