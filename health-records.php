<?php
session_start();
require_once 'db_connect.php';
require_once 'cloudinary_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Pet Owner';

// Handle Add Record Submission
// Handle Add Record Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_record'])) {
    $pet_id = $_POST['pet_id'];
    $type = $_POST['record_type'];
    $date = $_POST['record_date'];
    $desc = $_POST['description'];

    // File Upload Handling (Cloudinary)
    $docPath = null;
    if (isset($_FILES['health_doc']) && $_FILES['health_doc']['error'] == 0) {
        $cloudUrl = uploadToCloudinary($_FILES['health_doc']['tmp_name'], 'petcloud/health_docs');
        if ($cloudUrl) {
            $docPath = $cloudUrl;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO health_records (user_id, pet_id, record_type, record_date, description, document_path) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $pet_id, $type, $date, $desc, $docPath])) {
        echo "<script>alert('Health record saved successfully! ✨'); window.location.href='health-records.php';</script>";
    }
}

// Handle Add Memory Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_memory'])) {
    $pet_id = $_POST['pet_id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['memory_date'];

    $imgPath = null;
    if (isset($_FILES['memory_image']) && $_FILES['memory_image']['error'] == 0) {
        $cloudUrl = uploadToCloudinary($_FILES['memory_image']['tmp_name'], 'petcloud/memories');
        if ($cloudUrl) {
            $imgPath = $cloudUrl;
        }
    }

    if ($imgPath) {
        $stmt = $pdo->prepare("INSERT INTO pet_memories (user_id, pet_id, title, image_path, description, memory_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $pet_id, $title, $imgPath, $desc, $date]);
        echo "<script>alert('Memory added to timeline! 📸'); window.location.href='health-records.php';</script>";
    }
}

// Handle Task Toggle (AJAX-like simple POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_task'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    $pdo->prepare("UPDATE daily_tasks SET is_done = ? WHERE id = ? AND user_id = ?")->execute([$status, $task_id, $user_id]);
    exit(); // Stop here for AJAX
}

// Handle Delete Task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];
    $pdo->prepare("DELETE FROM daily_tasks WHERE id = ? AND user_id = ?")->execute([$task_id, $user_id]);
    exit();
}

// Handle Add New Task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_new_task'])) {
    $task_name = $_POST['task_name'];
    $task_time = $_POST['task_time'] ?? 'Just now';
    $freq = $_POST['frequency'] ?? 'Once';

    // We use task_date as the reference date
    $pdo->prepare("INSERT INTO daily_tasks (user_id, task_name, task_time, task_date, frequency) VALUES (?, ?, ?, CURDATE(), ?)")
        ->execute([$user_id, $task_name, $task_time, $freq]);
    exit();
}

// Fetch User's Pets
$petsStmt = $pdo->prepare("SELECT * FROM user_pets WHERE user_id = ?");
$petsStmt->execute([$user_id]);
$allPets = $petsStmt->fetchAll();

// Fetch Real Daily Tasks (Filtered by Frequency)
// Logic:
// - Once: matches today exactly
// - Daily: always matches
// - Weekly: matches same day of week as task_date
// - Monthly: matches same day of month as task_date
$sql = "SELECT * FROM daily_tasks 
        WHERE user_id = ? 
        AND (
            (frequency = 'Once' AND task_date = CURDATE()) OR
            (frequency = 'Daily') OR
            (frequency = 'Weekly' AND DAYOFWEEK(task_date) = DAYOFWEEK(CURDATE())) OR
            (frequency = 'Monthly' AND DAYOFMONTH(task_date) = DAYOFMONTH(CURDATE()))
        )";

$tasksStmt = $pdo->prepare($sql);
$tasksStmt->execute([$user_id]);
$dailyTasks = $tasksStmt->fetchAll();

$tasksCompleted = count(array_filter($dailyTasks, fn($t) => $t['is_done']));
$totalTasks = count($dailyTasks) ?: 1;
$progress = round(($tasksCompleted / $totalTasks) * 100);

// --- Filter Logic ---
$selectedPetId = $_GET['pet_id'] ?? null;
$selectedPet = null;
$healthRecords = [];
$memories = [];

if ($selectedPetId) {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM user_pets WHERE id = ? AND user_id = ?");
    $stmt->execute([$selectedPetId, $user_id]);
    $selectedPet = $stmt->fetch();

    if ($selectedPet) {
        // Fetch Records
        $recStmt = $pdo->prepare("SELECT * FROM health_records WHERE pet_id = ? ORDER BY record_date DESC");
        $recStmt->execute([$selectedPetId]);
        $healthRecords = $recStmt->fetchAll();

        // Fetch Memories
        $memStmt = $pdo->prepare("SELECT * FROM pet_memories WHERE pet_id = ? ORDER BY memory_date DESC");
        $memStmt->execute([$selectedPetId]);
        $memories = $memStmt->fetchAll();
    }
} else {
    // Fetch all records if no pet selected (Overview mode)
    $recStmt = $pdo->prepare("SELECT hr.*, p.pet_name FROM health_records hr JOIN user_pets p ON hr.pet_id = p.id WHERE hr.user_id = ? ORDER BY hr.record_date DESC LIMIT 5");
    $recStmt->execute([$user_id]);
    $healthRecords = $recStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Dashboard - PetCloud</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="css/styles.css">

    <style>
        /* Specific Styles for Health Page */
        :root {
            --primary: #3b82f6;
            --primary-light: #eff6ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        /* Hero */
        .hero {
            border-radius: 1.5rem;
            overflow: hidden;
            position: relative;
            height: 320px;
            color: white;
            margin-bottom: 2rem;
        }

        .hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }

        .hero-content {
            position: absolute;
            top: 0;
            left: 0;
            padding: 3rem;
            width: 70%;
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .hero h1 {
            font-family: 'Outfit';
            font-size: 2.75rem;
            line-height: 1.1;
            margin-bottom: 1rem;
        }

        .hero p {
            opacity: 0.9;
            margin-bottom: 2rem;
            font-size: 1rem;
            line-height: 1.5;
        }

        .search-wrap {
            position: relative;
            max-width: 500px;
            display: flex;
            gap: 0.5rem;
        }

        .search-input {
            flex: 1;
            padding: 0.8rem 1.25rem;
            border-radius: 0.75rem;
            border: none;
            font-size: 0.9rem;
            outline: none;
        }

        .search-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* Quick Actions */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1.25rem;
            border: 1px solid var(--gray-100);
            text-align: center;
            transition: 0.3s;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.25rem;
        }

        /* Layout Grid */
        .layout-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        /* Reminder */
        .reminder-card {
            background: white;
            border: 1px solid var(--gray-100);
            border-radius: 1rem;
            margin-bottom: 2.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .reminder-header {
            padding: 1rem 1.5rem;
            background: var(--gray-50);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--gray-100);
        }

        .reminder-body {
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .reminder-img {
            width: 220px;
            height: 140px;
            border-radius: 1rem;
            object-fit: cover;
        }

        .reminder-content h3 {
            font-family: 'Outfit';
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
            color: var(--gray-900);
        }

        .reminder-content p {
            color: var(--gray-500);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }

        /* Sidebar Cards */
        .side-card {
            background: white;
            border: 1px solid var(--gray-100);
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .side-card h3 {
            font-family: 'Outfit';
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-900);
        }

        .task-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.25rem;
            align-items: flex-start;
        }

        .task-check {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
        }

        .task-check.done {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .task-info h4 {
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
            color: var(--gray-900);
        }

        .task-info p {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* Pet Profile Cards */
        .profiles-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
        }

        .scroll-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 4rem;
        }

        .pet-card {
            background: white;
            border: 1px solid var(--gray-100);
            border-radius: 1.25rem;
            padding: 1.5rem;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .pet-top {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .pet-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            object-fit: cover;
        }

        .pet-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.8rem;
            border-bottom: 1px solid var(--gray-50);
            padding-bottom: 0.5rem;
        }

        .pet-line span:first-child {
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pet-line span:last-child {
            font-weight: 600;
            color: var(--gray-900);
        }

        /* Progress Circle */
        .progress-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: conic-gradient(var(--primary) calc(var(--p) * 1%), var(--gray-100) 0);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress-circle::after {
            content: attr(data-p) '%';
            position: absolute;
            width: 38px;
            height: 38px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--gray-700);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid var(--gray-100);
            border-radius: 0.75rem;
            outline: none;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
        }
    </style>
</head>

<body class="dashboard-page">

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'user-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <button class="menu-toggle-btn" onclick="if(window.toggleUserSidebar) window.toggleUserSidebar();">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search health records...">
                </div>
                <div class="header-actions">
                    <a href="mypets.php" class="btn"
                        style="background: #4b5e71; color: white; padding: 0.75rem 1.75rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px; text-decoration:none;">
                        ADD A PET
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <?php if ($selectedPet): ?>
                    <div style="margin-bottom:2rem; display:flex; align-items:center; gap:1rem;">
                        <a href="health-records.php"
                            style="background:white; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gray-900); box-shadow:0 2px 5px rgba(0,0,0,0.05); text-decoration:none;">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <h1 style="font-family:'Outfit'; margin:0;">
                            <?php echo htmlspecialchars($selectedPet['pet_name']); ?>'s Profile
                        </h1>
                        <button onclick="openModal()" class="btn btn-primary" style="margin-left:auto;"><i
                                class="fa-solid fa-plus"></i> Add Record</button>
                        <button onclick="openMemoryModal()" class="btn btn-outline" style="margin-left:1rem;"><i
                                class="fa-solid fa-camera"></i> Add Memory</button>
                    </div>

                    <style>
                        .tab-link {
                            padding: 0.5rem 0;
                            color: var(--gray-500);
                            text-decoration: none;
                            border-bottom: 2px solid transparent;
                            font-weight: 600;
                            font-size: 0.95rem;
                        }

                        .tab-link.active {
                            color: var(--primary);
                            border-bottom-color: var(--primary);
                        }
                    </style>

                    <div style="display:flex; gap:2rem; border-bottom:1px solid var(--gray-200); margin-bottom:2rem;">
                        <a href="javascript:void(0)" class="tab-link active" onclick="switchTab(event, 'health-tab')">Health
                            Records</a>
                        <a href="javascript:void(0)" class="tab-link" onclick="switchTab(event, 'memories-tab')">Memories
                            Gallery</a>
                    </div>

                    <!-- HEALTH TAB -->
                    <div id="health-tab" class="tab-content">
                        <?php if (empty($healthRecords)): ?>
                            <div
                                style="text-align:center; padding:3rem; color:var(--gray-500); background:white; border-radius:1rem;">
                                <i class="fa-solid fa-folder-open" style="font-size:2rem; margin-bottom:1rem; opacity:0.5;"></i>
                                <p>No health records found.</p>
                            </div>
                        <?php else: ?>
                            <div
                                style="background:white; border-radius:1rem; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead style="background:var(--gray-50);">
                                        <tr>
                                            <th
                                                style="padding:1rem; text-align:left; font-size:0.85rem; color:var(--gray-500);">
                                                Type</th>
                                            <th
                                                style="padding:1rem; text-align:left; font-size:0.85rem; color:var(--gray-500);">
                                                Date</th>
                                            <th
                                                style="padding:1rem; text-align:left; font-size:0.85rem; color:var(--gray-500);">
                                                Details</th>
                                            <th
                                                style="padding:1rem; text-align:left; font-size:0.85rem; color:var(--gray-500);">
                                                Document</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($healthRecords as $rec): ?>
                                            <tr style="border-bottom:1px solid var(--gray-100);">
                                                <td style="padding:1rem; font-weight:600; color:var(--gray-900);">
                                                    <?php echo htmlspecialchars($rec['record_type']); ?>
                                                </td>
                                                <td style="padding:1rem; color:var(--gray-500);">
                                                    <?php echo htmlspecialchars($rec['record_date']); ?>
                                                </td>
                                                <td style="padding:1rem; color:var(--gray-700);">
                                                    <?php echo htmlspecialchars($rec['description']); ?>
                                                </td>
                                                <td style="padding:1rem;">
                                                    <?php if (!empty($rec['document_path'])): ?>
                                                        <a href="<?php echo htmlspecialchars($rec['document_path']); ?>" target="_blank"
                                                            style="color:var(--primary); text-decoration:none; font-weight:600; font-size:0.85rem;">
                                                            <i class="fa-solid fa-file-pdf"></i> View
                                                        </a>
                                                    <?php else: ?>
                                                        <span style="color:var(--gray-200);">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- MEMORIES TAB -->
                    <div id="memories-tab" class="tab-content" style="display:none;">
                        <?php if (empty($memories)): ?>
                            <div
                                style="text-align:center; padding:3rem; color:var(--gray-500); background:white; border-radius:1rem;">
                                <i class="fa-solid fa-images" style="font-size:2rem; margin-bottom:1rem; opacity:0.5;"></i>
                                <p>No memories yet. Add your first photo!</p>
                            </div>
                        <?php else: ?>
                            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:1.5rem;">
                                <?php foreach ($memories as $mem): ?>
                                    <div
                                        style="background:white; border-radius:1rem; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); transition:transform 0.2s;">
                                        <div style="height:200px; overflow:hidden;">
                                            <img src="<?php echo htmlspecialchars($mem['image_path']); ?>"
                                                style="width:100%; height:100%; object-fit:cover;">
                                        </div>
                                        <div style="padding:1rem;">
                                            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                                                <h4 style="font-family:'Outfit'; font-size:1rem; margin:0;">
                                                    <?php echo htmlspecialchars($mem['title']); ?>
                                                </h4>
                                                <span
                                                    style="font-size:0.75rem; color:var(--gray-400);"><?php echo htmlspecialchars($mem['memory_date']); ?></span>
                                            </div>
                                            <p style="color:var(--gray-500); font-size:0.85rem; line-height:1.5; margin:0;">
                                                <?php echo htmlspecialchars($mem['description']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>

                    <!-- Hero -->
                    <section class="hero"
                        style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1576201836106-ca1746f3364d?w=1200&q=80'); background-size: cover; background-position: center;">
                        <div class="hero-content">
                            <span class="hero-badge"
                                style="background: #1e293b; color: white; border: 1px solid rgba(255,255,255,0.1);">VETERINARY
                                APPROVED</span>
                            <h1 style="text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                                Expert Health Guidance<br>for Every <span style="color: #3b82f6;">Paw Step</span></h1>
                            <p>Access comprehensive care guides, track vital health milestones, and get personalized advice
                                for your furry companions.</p>

                        </div>
                    </section>

                    <!-- Quick Actions -->
                    <section class="stats-grid">
                        <div class="stat-card" onclick="openModal()">
                            <div class="stat-icon" style="background:#dbeafe; color:#3b82f6;"><i
                                    class="fa-solid fa-syringe"></i>
                            </div>
                            <h4 style="font-size:0.9rem; margin-bottom:0.25rem; font-weight:700;">Log Vaccination</h4>
                            <p style="font-size:0.7rem; color:var(--gray-500);">Update health records</p>
                        </div>
                        <a href="find-vet.php" class="stat-card" style="text-decoration:none; color:inherit;">
                            <div class="stat-icon" style="background:#f0fdf4; color:#22c55e;"><i
                                    class="fa-solid fa-location-dot"></i></div>
                            <h4 style="font-size:0.9rem; margin-bottom:0.25rem; font-weight:700;">Find Vet</h4>
                            <p style="font-size:0.7rem; color:var(--gray-500);">Clinics nearby</p>
                        </a>
                        <a href="mypets.php" class="stat-card" style="text-decoration:none; color:inherit;">
                            <div class="stat-icon" style="background:#fef9c3; color:#eab308;"><i
                                    class="fa-solid fa-plus"></i></div>
                            <h4 style="font-size:0.9rem; margin-bottom:0.25rem; font-weight:700;">Add Pet</h4>
                            <p style="font-size:0.7rem; color:var(--gray-500);">Create new profile</p>
                        </a>
                        <a href="symptom-checker.php" class="stat-card" style="text-decoration:none; color:inherit;">
                            <div class="stat-icon" style="background:#fee2e2; color:#ef4444;"><i
                                    class="fa-solid fa-heart-pulse"></i></div>
                            <h4 style="font-size:0.9rem; margin-bottom:0.25rem; font-weight:700;">Symptom Checker</h4>
                            <p style="font-size:0.7rem; color:var(--gray-500);">AI Health Assistant</p>
                        </a>
                    </section>

                    <div class="layout-grid">
                        <div class="main-side">
                            <!-- Urgent Reminder -->
                            <div class="reminder-card" id="reminderCard">
                                <div class="reminder-header">
                                    <div style="display:flex; align-items:center; gap:0.75rem;">
                                        <i class="fa-solid fa-circle-exclamation" style="color:var(--danger);"></i>
                                        <span style="font-weight:700; font-size:0.9rem; color:var(--gray-900);">Urgent
                                            Reminder</span>
                                    </div>
                                    <span
                                        style="font-size:0.75rem; color:var(--danger); font-weight:600; background:#fee2e2; padding:0.2rem 0.6rem; border-radius:1rem;">Due
                                        in 12 days</span>
                                </div>
                                <div class="reminder-body">
                                    <img src="https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400"
                                        class="reminder-img">
                                    <div class="reminder-content">
                                        <h3>Rabies Booster Shot</h3>
                                        <p style="margin-bottom:0.25rem;">For: <strong
                                                style="color:var(--gray-900);">Bella</strong> (Golden Retriever)</p>
                                        <p>This vaccination is critical for legal requirements and your pet's safety. Please
                                            schedule an appointment with Dr. Smith soon.</p>
                                        <div style="display:flex; gap:0.75rem;">
                                            <a href="schedule.php" class="btn btn-primary"
                                                style="text-decoration:none;">Book
                                                Appointment</a>
                                            <button class="btn btn-outline" onclick="dismissReminder()">Mark as
                                                Done</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-side">
                            <div class="side-card">
                                <h3 style="margin-bottom:0.5rem;">Daily Routine</h3>
                                <p style="font-size:0.75rem; color:var(--gray-500); margin-bottom:1.5rem;">Keep on track
                                    today</p>

                                <div style="display:flex; justify-content:flex-end; margin-top:-3rem; margin-bottom:2rem;">
                                    <div class="progress-circle" id="taskProgressCircle" data-p="<?php echo $progress; ?>"
                                        style="--p:<?php echo $progress; ?>;"></div>
                                </div>

                                <div class="tasks-list">
                                    <?php foreach ($dailyTasks as $task): ?>
                                        <div class="task-item" style="position:relative;">
                                            <div class="task-check <?php echo $task['is_done'] ? 'done' : ''; ?>"
                                                onclick="toggleTask(this.parentElement, <?php echo $task['id']; ?>)">
                                                <?php if ($task['is_done']): ?><i class="fa-solid fa-check"
                                                        style="font-size:10px;"></i><?php endif; ?>
                                            </div>
                                            <div class="task-info"
                                                onclick="toggleTask(this.parentElement, <?php echo $task['id']; ?>)"
                                                style="flex:1; cursor:pointer;">
                                                <h4><?php echo htmlspecialchars($task['task_name']); ?></h4>
                                                <p class="task-time">
                                                    <?php echo htmlspecialchars($task['task_time']); ?>
                                                    <?php if ($task['frequency'] != 'Once'): ?>
                                                        <span
                                                            style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:4px; font-size:0.65rem; margin-left:0.5rem; text-transform:uppercase;">
                                                            <?php echo htmlspecialchars($task['frequency']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <button class="delete-btn" onclick="deleteTask(<?php echo $task['id']; ?>)"
                                                title="Delete Task"
                                                style="background:none; border:none; color:#ef4444; opacity:0.5; cursor:pointer; padding:0.5rem; transition:0.2s;">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button onclick="addTask()"
                                    style="width:100%; padding:0.75rem; background:transparent; border:1px dashed var(--gray-200); border-radius:0.75rem; color:var(--primary); font-size:0.8rem; font-weight:600; cursor:pointer;">+
                                    Add Task</button>
                            </div>
                        </div>
                    </div>

                    <div class="profiles-header">
                        <div>
                            <h2 style="font-family:'Outfit'; color:var(--gray-900);">Your Pet Profiles</h2>
                            <p style="font-size:0.8rem; color:var(--gray-500);">Manage individual needs and schedules</p>
                        </div>
                    </div>

                    <div class="scroll-grid">
                        <?php if (empty($allPets)): ?>
                            <div style="grid-column: 1/-1; text-align:center; padding: 2rem; color: var(--gray-500);">
                                No pets added yet. Start by adding a pet!
                            </div>
                        <?php else: ?>
                            <?php foreach ($allPets as $pet): ?>
                                <div class="pet-card">
                                    <div class="pet-top">
                                        <img src="<?php echo htmlspecialchars($pet['pet_image']); ?>" class="pet-avatar">
                                        <div>
                                            <h4 style="font-family:'Outfit'; color:var(--gray-900);">
                                                <?php echo htmlspecialchars($pet['pet_name']); ?>
                                            </h4>
                                            <p style="font-size:0.7rem; color:var(--gray-500);">
                                                <?php echo htmlspecialchars($pet['pet_breed']); ?> •
                                                <?php echo htmlspecialchars($pet['pet_age']); ?>
                                            </p>
                                        </div>
                                        <i class="fa-solid fa-ellipsis" style="margin-left:auto; color:var(--gray-200);"></i>
                                    </div>
                                    <div class="pet-line">
                                        <span><i class="fa-solid fa-weight-scale"></i> Weight</span>
                                        <span><?php echo htmlspecialchars($pet['pet_weight'] ?? '28.5 kg'); ?></span>
                                    </div>
                                    <div class="pet-line" style="border:none;">
                                        <span><i class="fa-regular fa-calendar-check"></i> Next Vet</span>
                                        <span style="color:var(--danger);">Oct 24</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <a href="mypets.php" class="pet-card"
                            style="border:2px dashed var(--gray-200); display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; cursor:pointer; text-decoration:none; color:inherit; box-shadow:none; background: transparent;">
                            <div
                                style="width:44px; height:44px; border-radius:50%; border:2px solid var(--primary-light); display:flex; align-items:center; justify-content:center; color:var(--primary); margin-bottom:0.75rem;">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <h4 style="font-family:'Outfit'; font-size:0.9rem; color:var(--gray-900);">Add New Pet</h4>
                            <p style="font-size:0.7rem; color:var(--gray-500);">Create profile</p>
                        </a>
                    </div>

                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Record Modal -->
    <div class="modal" id="recordModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div class="modal-content"
            style="background:white; width:90%; max-width:500px; padding:2rem; border-radius:1.5rem; position:relative;">
            <div class="modal-header"
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 style="font-family:'Outfit'; color:var(--gray-900);">Log Health Record</h2>
                <i class="fa-solid fa-xmark" style="cursor:pointer; font-size:1.5rem;" onclick="closeModal()"></i>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Select Pet</label>
                    <select class="form-control" name="pet_id" required>
                        <?php foreach ($allPets as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($selectedPetId == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['pet_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Record Type</label>
                    <select class="form-control" name="record_type" required>
                        <option>Vaccination</option>
                        <option>Weight Check</option>
                        <option>Check-up</option>
                        <option>Surgery</option>
                        <option>Medication</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" name="record_date" value="<?php echo date('Y-m-d'); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label>Document (Optional)</label>
                    <input type="file" class="form-control" name="health_doc" accept=".pdf,.jpg,.jpeg,.png">
                    <small style="color:gray;">Upload vaccination certs, prescriptions, etc.</small>
                </div>
                <div class="form-group">
                    <label>Description/Notes</label>
                    <textarea class="form-control" name="description" rows="3"
                        placeholder="e.g. Rabies booster, 3-year dose"></textarea>
                </div>
                <button type="submit" name="add_record" class="btn btn-primary"
                    style="width:100%; margin-top:1rem;">Save Record</button>
            </form>
        </div>
    </div>

    <!-- Add Memory Modal -->
    <div class="modal" id="memoryModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div class="modal-content"
            style="background:white; width:90%; max-width:500px; padding:2rem; border-radius:1.5rem; position:relative;">
            <div class="modal-header"
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 style="font-family:'Outfit'; color:var(--gray-900);">Add New Memory 📸</h2>
                <i class="fa-solid fa-xmark" style="cursor:pointer; font-size:1.5rem;" onclick="closeMemoryModal()"></i>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="pet_id" value="<?php echo $selectedPetId; ?>">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" required placeholder="e.g. First day home">
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" name="memory_date" value="<?php echo date('Y-m-d'); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" class="form-control" name="memory_image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description" rows="3"
                        placeholder="Tell the story..."></textarea>
                </div>
                <button type="submit" name="add_memory" class="btn btn-primary"
                    style="width:100%; margin-top:1rem;">Save Memory</button>
            </form>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal" id="addTaskModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div class="modal-content"
            style="background:white; width:90%; max-width:400px; padding:2rem; border-radius:1.5rem; position:relative;">
            <div class="modal-header"
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 style="font-family:'Outfit'; color:var(--gray-900);">Add Daily Task</h2>
                <i class="fa-solid fa-xmark" style="cursor:pointer; font-size:1.5rem;" onclick="closeTaskModal()"></i>
            </div>
            <form id="addTaskForm" onsubmit="event.preventDefault(); saveTask();">
                <div class="form-group">
                    <label>Task Name</label>
                    <input type="text" class="form-control" id="newTaskName" required placeholder="e.g. Feed the dog">
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="time" class="form-control" id="newTaskTime" required>
                </div>
                <div class="form-group">
                    <label>Frequency (Reminder)</label>
                    <select class="form-control" id="newTaskFrequency">
                        <option value="Daily">Daily</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Once">Once Only</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Add Task</button>
            </form>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="toast"
        style="visibility:hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 16px; position: fixed; z-index: 3000; left: 50%; bottom: 30px; transform: translateX(-50%); font-size: 17px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fa-solid fa-circle-check" style="color: #4ade80; margin-right: 8px;"></i> <span id="toastMsg">Task
            Added Successfully!</span>
    </div>

    <!-- Alarm Audio -->
    <audio id="alarmSound" preload="auto">
        <source src="https://assets.mixkit.co/service/sfx/preview/2869.mp3" type="audio/mpeg">
    </audio>

    <style>
        /* Toast Animation */
        @keyframes fadein {
            from {
                bottom: 0;
                opacity: 0;
            }

            to {
                bottom: 30px;
                opacity: 1;
            }
        }

        @keyframes fadeout {
            from {
                bottom: 30px;
                opacity: 1;
            }

            to {
                bottom: 0;
                opacity: 0;
            }
        }
    </style>

    <script>
        function openModal() { document.getElementById('recordModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('recordModal').style.display = 'none'; }

        function dismissReminder() {
            const card = document.getElementById('reminderCard');
            card.style.transform = 'scale(0.95)';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 400);
        }

        async function toggleTask(el, taskId) {
            // Check if click was on delete button
            if (event.target.closest('.delete-btn')) return;

            const check = el.querySelector('.task-check');
            const isDone = !check.classList.contains('done');

            // Visual feedback
            check.classList.toggle('done');
            check.innerHTML = isDone ? '<i class="fa-solid fa-check" style="font-size:10px;"></i>' : '';

            // DB Update
            const formData = new FormData();
            formData.append('toggle_task', '1');
            formData.append('task_id', taskId);
            formData.append('status', isDone ? '1' : '0');

            await fetch('health-records.php', { method: 'POST', body: formData });
            // updateProgress() is handled on page load in PHP, or effectively by visual toggle
            // For a real app, re-calculating the circle would be good, but simple visual toggle is okay for now
            // as re-calculating the circle requires counting visual elements
            updateProgressCircle();
        }

        async function deleteTask(taskId) {
            if (!confirm('Are you sure you want to delete this task?')) return;

            const formData = new FormData();
            formData.append('delete_task', '1');
            formData.append('task_id', taskId);

            try {
                await fetch('health-records.php', { method: 'POST', body: formData });
                showToast("Task Deleted 🗑️");
                setTimeout(() => location.reload(), 1000);
            } catch (error) {
                console.error('Error deleting task:', error);
                alert('Failed to delete task');
            }
        }

        function updateProgressCircle() {
            const total = document.querySelectorAll('.task-item').length;
            const done = document.querySelectorAll('.task-check.done').length;
            const percent = total > 0 ? Math.round((done / total) * 100) : 0;
            const circle = document.getElementById('taskProgressCircle');
            if (circle) {
                circle.style.setProperty('--p', percent);
                circle.setAttribute('data-p', percent);
            }
        }

        function performSearch() {
            const q = document.getElementById('mainSearch').value.toLowerCase();
            // Since article cards were removed in the source provided, this might not target anything visible
            // But we keep the function to avoid breaking listeners
            alert('Search functionality for articles is currently integrating with the new dashboard.');
        }

        // --- NEW: Modal Functions ---
        function openTaskModal() { document.getElementById('addTaskModal').style.display = 'flex'; }
        function closeTaskModal() { document.getElementById('addTaskModal').style.display = 'none'; }

        function addTask() {
            openTaskModal();
        }

        async function saveTask() {
            const name = document.getElementById('newTaskName').value;
            const time = document.getElementById('newTaskTime').value;
            const freq = document.getElementById('newTaskFrequency').value;

            if (!name || !time) return;

            const formData = new FormData();
            formData.append('add_new_task', '1');
            formData.append('task_name', name);
            formData.append('task_time', time);
            formData.append('frequency', freq);

            try {
                await fetch('health-records.php', { method: 'POST', body: formData });
                closeTaskModal();
                showToast("Task Added Successfully! 🔔");
                setTimeout(() => location.reload(), 1500);
            } catch (error) {
                console.error('Error adding task:', error);
                alert('Failed to add task');
            }
        }

        function showToast(message) {
            const x = document.getElementById("toast");
            if (x) {
                document.getElementById("toastMsg").innerText = message;
                x.style.visibility = "visible";
                x.style.animation = "fadein 0.5s, fadeout 0.5s 2.5s";
                setTimeout(function () { x.style.visibility = "hidden"; }, 3000);
            }
        }

        // --- NEW: Alarm System ---
        setInterval(checkAlarms, 30000);

        function checkAlarms() {
            const now = new Date();
            const currentHours = String(now.getHours()).padStart(2, '0');
            const currentMinutes = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${currentHours}:${currentMinutes}`;
            const currentDay = now.getDay(); // 0-6
            const currentDate = now.getDate(); // 1-31

            const tasks = document.querySelectorAll('.task-item');
            tasks.forEach(task => {
                const check = task.querySelector('.task-check');
                if (check && !check.classList.contains('done')) {
                    const timeEl = task.querySelector('.task-time');
                    const freqEl = task.querySelector('.task-freq'); // Hidden field ideally

                    const timeText = timeEl ? timeEl.innerText.trim() : ''; // "08:00 AM" or "08:00"

                    // Simple check if time matches
                    // Note: Ideally compare parsed time. Assuming format matches.
                    if (timeText.includes(currentTime)) {
                        const h4 = task.querySelector('h4');
                        triggerAlarm(h4 ? h4.innerText : 'Task');
                    }
                }
            });
        }

        function triggerAlarm(taskName) {
            const sound = document.getElementById('alarmSound');
            if (sound) sound.play().catch(e => console.log("Audio play failed:", e));
            alert(`⏰ ALARM: It's time for "${taskName}"!`);
        }

        if ("Notification" in window) {
            Notification.requestPermission();
        }

        // --- Pet Profile Functions ---
        function openMemoryModal() { document.getElementById('memoryModal').style.display = 'flex'; }
        function closeMemoryModal() { document.getElementById('memoryModal').style.display = 'none'; }

        function switchTab(e, tabId) {
            e.preventDefault();
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-link').forEach(el => el.classList.remove('active'));

            document.getElementById(tabId).style.display = 'block';
            e.target.classList.add('active');
        }
    </script>
</body>

</html>