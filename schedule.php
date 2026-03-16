<?php
session_start();
require_once 'db_connect.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Pet Lover';
$user_pic = $_SESSION['profile_pic'] ?? 'images/default_user.png';

$success = "";
$error = "";

// --- AUTO-FIX: Create Tables/Columns If Missing ---
try {
    $pdo->query("SELECT payment_id FROM appointments LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->query("SELECT 1 FROM appointments LIMIT 1");
        $pdo->exec("ALTER TABLE appointments ADD COLUMN payment_id VARCHAR(255) AFTER user_id");
    } catch (PDOException $ex) {
        // Table might not exist, but let booking logic handle it or run setup_appointment_system.php
    }
}

// Handle Actions (Cancel Appointment)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cancel_appointment'])) {
        $appt_id = $_POST['appointment_id'];
        // Cancel appointment safely
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$appt_id, $user_id])) {
            $success = "Appointment cancelled successfully.";
        } else {
            $error = "Failed to cancel appointment.";
        }
        // Redirect to avoid resubmission
        header("Location: schedule.php?msg=cancelled");
        exit();
    } elseif (isset($_POST['confirm_booking'])) {
        // Handle Booking
        $pet_name = $_POST['pet_name'] ?? 'Pet';
        $breed = $_POST['breed'] ?? 'Unknown';
        $service_type = $_POST['service_type'] ?? 'General';
        $date = $_POST['appointment_date'];
        $time = $_POST['appointment_time'];
        $hospital_id = $_POST['hospital_id'];
        $payment_id = $_POST['razorpay_payment_id'] ?? '';

        if (empty($payment_id)) {
            $error = "Payment configuration error or session timeout.";
        } else {
            // Security: Re-fetch price from DB to prevent tampering
            $priceStmt = $pdo->prepare("SELECT price FROM hospital_services WHERE hospital_id = ? AND service_name = ?");
            $priceStmt->execute([$hospital_id, $service_type]);
            $priceRow = $priceStmt->fetch();

            $cost = $priceRow ? $priceRow['price'] : 0;

            try {
                // Insert with hospital_id
                $stmt = $pdo->prepare("
                    INSERT INTO appointments 
                    (user_id, payment_id, hospital_id, pet_name, breed, service_type, title, appointment_date, appointment_time, description, cost, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
                ");

                $title = $service_type . " for " . $pet_name;

                if ($stmt->execute([$user_id, $payment_id, $hospital_id, $pet_name, $breed, $service_type, $title, $date, $time, "Scheduled Appointment", $cost])) {
                    $success = "Booking confirmed for " . $pet_name . "! ✨";
                }
            } catch (PDOException $e) {
                $error = "Booking failed: " . $e->getMessage();
            }
        }
    }
}

// Fetch user pets
$petsStmt = $pdo->prepare("SELECT * FROM user_pets WHERE user_id = ?");
$petsStmt->execute([$user_id]);
$allPets = $petsStmt->fetchAll();

// Fetch ALL upcoming appointments
$apptStmt = $pdo->prepare("
    SELECT a.*, h.name as hospital_name, h.image_url as hospital_image
    FROM appointments a 
    LEFT JOIN hospitals h ON a.hospital_id = h.id 
    WHERE a.user_id = ? AND a.status != 'cancelled' 
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$apptStmt->execute([$user_id]);
$appointments = $apptStmt->fetchAll();

// Calculate stats
$upcomingCount = 0;
foreach ($appointments as $a) {
    if (strtotime($a['appointment_date']) >= strtotime('today'))
        $upcomingCount++;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - PetCloud</title>

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
        /* Specific Styles for Schedule Page */
        .schedule-container {
            display: grid;
            gap: 2rem;
        }

        .booking-form-wrapper {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            border: 1px solid #e2e8f0;
        }

        /* Reusing styles from previous schedule.php but scoped */
        .step-badge {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
        }

        .step-dot {
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
        }

        .section-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.25rem;
            margin-top: 2rem;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }

        .service-option {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
            background: white;
        }

        .service-option:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .service-option.active {
            border: 2px solid #3b82f6;
            background: #eff6ff;
        }

        .service-option i {
            display: block;
            font-size: 1.5rem;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .service-option.active i {
            color: #3b82f6;
        }

        .service-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
        }

        .time-panel {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        .time-slot {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 600;
            background: white;
            cursor: pointer;
            transition: 0.2s;
        }

        .time-slot:hover {
            border-color: #3b82f6;
        }

        .time-slot.active {
            border: 2px solid #3b82f6;
            background: #eff6ff;
            color: #3b82f6;
        }

        .time-slot.disabled {
            opacity: 0.5;
            pointer-events: none;
            background: #f1f5f9;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
    </style>
</head>

<body class="dashboard-page">

    <?php if ($success): ?>
        <div class="overlay" onclick="window.location.href='schedule.php'">
            <div>
                <div
                    style="width: 80px; height: 80px; background: #dcfce7; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 2rem;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h1 style="font-family:'Outfit'; margin-bottom: 1rem;">Confirmed!</h1>
                <p style="color: #64748b; margin-bottom: 2rem;"><?php echo $success; ?></p>
                <a href="schedule.php" class="btn"
                    style="background:var(--primary); color:white; padding:0.75rem 1.5rem; border-radius:1rem; text-decoration:none;">View
                    Schedule</a>
            </div>
        </div>
    <?php endif; ?>

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
                    <input type="text" placeholder="Search appointments...">
                </div>
                <div class="header-actions">
                    <a href="mypets.php" class="btn"
                        style="background: #4b5e71; color: white; padding: 0.75rem 1.75rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px; text-decoration:none;">
                        ADD A PET
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="schedule-container">

                    <!-- 1. My Schedule List -->
                    <div class="appointments-list-section">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                            <h2 style="font-family:'Outfit'; font-size:1.5rem; color:#1e293b;">My Schedule</h2>
                            <button
                                onclick="document.getElementById('bookingSection').scrollIntoView({behavior: 'smooth'})"
                                class="btn"
                                style="background:var(--primary); color:white; padding:0.6rem 1.2rem; border-radius:0.75rem; font-weight:600; cursor:pointer; border:none;">
                                <i class="fa-solid fa-plus"></i> New Appointment
                            </button>
                        </div>

                        <?php if (empty($appointments)): ?>
                            <div
                                style="text-align: center; padding: 4rem 2rem; background:white; border-radius:1.5rem; border:1px dashed #cbd5e1;">
                                <div
                                    style="width:60px; height:60px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; color:#94a3b8; font-size:1.5rem;">
                                    <i class="fa-regular fa-calendar"></i>
                                </div>
                                <h3 style="color:#64748b; font-size:1rem; margin-bottom:0.5rem;">No appointments scheduled
                                </h3>
                                <p style="color:#94a3b8; font-size:0.9rem; max-width:300px; margin:0 auto;">Ready to book a
                                    checkup, grooming session, or playdate?</p>
                            </div>
                        <?php else: ?>
                            <div class="schedule-list" style="display:grid; grid-template-columns:1fr; gap:1rem;">
                                <?php foreach ($appointments as $appt):
                                    $apptDate = new DateTime($appt['appointment_date']);
                                    $isPast = $apptDate < new DateTime('today');
                                    $statusColor = $isPast ? '#94a3b8' : '#10b981';
                                    $bg = $isPast ? '#f8fafc' : 'white';
                                    $border = $isPast ? '#e2e8f0' : '#e2e8f0';
                                    ?>
                                    <div class="appt-card"
                                        style="background:<?php echo $bg; ?>; border:1px solid <?php echo $border; ?>; padding:1.5rem; border-radius:1rem; display:flex; align-items:center; gap:1.5rem;">
                                        <!-- Date Box -->
                                        <div
                                            style="background:white; border:1px solid #e2e8f0; padding:0.8rem 1rem; border-radius:0.75rem; text-align:center; min-width:80px; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
                                            <div style="font-weight:700; color:<?php echo $statusColor; ?>; font-size:1.25rem;">
                                                <?php echo $apptDate->format('d'); ?>
                                            </div>
                                            <div
                                                style="font-size:0.75rem; color:#64748b; text-transform:uppercase; font-weight:600;">
                                                <?php echo $apptDate->format('M'); ?>
                                            </div>
                                        </div>

                                        <!-- Info -->
                                        <div style="flex:1;">
                                            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                                                <h3 style="font-family:'Outfit'; font-size:1.1rem; margin:0; color:#1e293b;">
                                                    <?php echo htmlspecialchars($appt['title'] ?? ''); ?>
                                                </h3>
                                                <?php if ($isPast): ?>
                                                    <span
                                                        style="background:#f1f5f9; color:#64748b; font-size:0.65rem; padding:2px 6px; border-radius:4px; font-weight:700;">COMPLETED</span>
                                                <?php endif; ?>
                                            </div>
                                            <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.5rem;">
                                                <i class="fa-regular fa-clock" style="margin-right:4px;"></i>
                                                <?php echo date('g:i A', strtotime($appt['appointment_time'])); ?>
                                                <span style="margin:0 8px; color:#cbd5e1;">|</span>
                                                <i class="fa-solid fa-location-dot" style="margin-right:4px;"></i>
                                                <?php echo htmlspecialchars($appt['hospital_name'] ?? 'PetCloud Partner'); ?>
                                            </p>
                                        </div>

                                        <!-- Actions -->
                                        <?php if (!$isPast): ?>
                                            <form method="POST"
                                                onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                                <input type="hidden" name="cancel_appointment" value="1">
                                                <button type="submit"
                                                    style="background:white; border:1px solid #fee2e2; color:#ef4444; padding:0.6rem 1rem; border-radius:0.75rem; font-weight:600; cursor:pointer; font-size:0.85rem; transition:0.2s;"
                                                    onmouseover="this.style.background='#fee2e2'"
                                                    onmouseout="this.style.background='white'">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>


                    <!-- 2. New Appointment Form -->
                    <div id="bookingSection" class="booking-form-wrapper">
                        <div class="form-header"
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; border-bottom:1px solid #f1f5f9; padding-bottom:1rem;">
                            <div>
                                <h2
                                    style="font-family:'Outfit'; font-size:1.5rem; color:#1e293b; margin-bottom:0.25rem;">
                                    Schedule New Appointment</h2>
                                <p style="color:#64748b; font-size:0.9rem;">Find the best care for your furry friend</p>
                            </div>
                            <div class="step-badge" id="stepBadge">
                                <div class="step-dot"></div> Step 1: Details
                            </div>
                        </div>

                        <form method="POST" id="bookingForm">
                            <input type="hidden" name="hospital_id" id="hospitalIdInput">
                            <input type="hidden" name="service_price" id="priceInput">

                            <!-- STEP 1: Pet & Service -->
                            <div id="step1">
                                <div class="section-label"><i class="fa-solid fa-paw"></i> Pet Details</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                                    <div class="form-group">
                                        <label
                                            style="display:block; font-size:0.9rem; font-weight:600; color:#334155; margin-bottom:0.5rem;">Pet
                                            Name</label>
                                        <input type="text" name="pet_name" id="petNameInput" class="form-control"
                                            style="width:100%; padding:0.75rem 1rem; border:1px solid #e2e8f0; border-radius:0.75rem;"
                                            placeholder="e.g. Bella" required>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            style="display:block; font-size:0.9rem; font-weight:600; color:#334155; margin-bottom:0.5rem;">Breed</label>
                                        <select name="breed" class="form-control"
                                            style="width:100%; padding:0.75rem 1rem; border:1px solid #e2e8f0; border-radius:0.75rem;"
                                            required>
                                            <option value="Dog">Dog</option>
                                            <option value="Cat">Cat</option>
                                            <option value="Bird">Bird</option>
                                            <option value="Rabbit">Rabbit</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="section-label"><i class="fa-solid fa-layer-group"></i> Select Category</div>
                                <div id="categoryGrid" class="service-grid">
                                    <div style="grid-column:1/-1; text-align:center; color:#94a3b8;">Loading...</div>
                                </div>

                                <div id="serviceSelectionSection" style="display:none; margin-top: 1.5rem;">
                                    <div class="section-label"><i class="fa-solid fa-briefcase-medical"></i> Select
                                        Service</div>
                                    <div id="serviceListGrid" class="service-grid"
                                        style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                                        <!-- Populated dynamically by JS -->
                                    </div>
                                </div>

                                <input type="hidden" name="service_type" id="serviceTypeInput" required>

                                <!-- Hospital Selection Container -->
                                <div id="hospitalSection" style="display:none; margin-top: 2rem;">
                                    <div class="section-label"><i class="fa-solid fa-hospital"></i> Select Clinic</div>
                                    <div id="hospitalGrid" style="display:grid; grid-template-columns:1fr; gap:1rem;">
                                        <!-- Populated by JS -->
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: Date & Time (Initially Hidden) -->
                            <div id="step2" style="display:none; margin-top: 2rem;">
                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;">
                                    <div>
                                        <div class="section-label"><i class="fa-solid fa-calendar"></i> Select Date
                                        </div>
                                        <input type="date" name="appointment_date" id="dateInput" class="form-control"
                                            style="width:100%; padding:0.75rem 1rem; border:1px solid #e2e8f0; border-radius:0.75rem; font-family:inherit;"
                                            min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div>
                                        <div class="section-label"><i class="fa-solid fa-clock"></i> Available Time
                                        </div>
                                        <div class="time-panel" id="timeSlotContainer">
                                            <div
                                                style="grid-column: 1/-1; text-align:center; color:#94a3b8; padding:1rem;">
                                                Select a date to view slots</div>
                                        </div>
                                        <input type="hidden" name="appointment_time" id="timeInput" required>
                                    </div>
                                </div>

                                <div
                                    style="background:#eff6ff; padding:1rem; border-radius:1rem; margin-top:1.5rem; display:flex; gap:0.75rem; color:#1e3a8a; font-size:0.9rem;">
                                    <i class="fa-solid fa-circle-info" style="color:#3b82f6; margin-top:2px;"></i>
                                    <div>Selected Clinic: <span id="selectedClinicName"
                                            style="font-weight:700;">-</span></div>
                                </div>
                            </div>

                            <div class="form-footer"
                                style="margin-top:2.5rem; display:flex; justify-content:space-between; align-items:center; padding-top:1.5rem; border-top:1px dashed #e2e8f0;">
                                <div class="total-box">
                                    <span
                                        style="display:block; font-size:0.75rem; color:#64748b; font-weight:600;">Total
                                        estimation</span>
                                    <div class="total-price" id="totalPriceDisplay"
                                        style="font-size:1.5rem; font-weight:700; font-family:'Outfit'; color:#0f172a;">
                                        ₹0</div>
                                </div>
                                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                                <input type="hidden" name="confirm_booking" value="1">
                                <button type="button" class="btn-confirm" id="btnContinue"
                                    style="background:#0f172a; color:white; padding:1rem 2rem; border-radius:1rem; border:none; font-weight:700; font-size:1rem; cursor:pointer; display:flex; align-items:center; gap:0.75rem; transition:0.3s;">
                                    Secure Payment & Book <i class="fa-solid fa-lock" style="font-size: 0.8rem;"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        // DOM Elements
        const categoryGrid = document.getElementById('categoryGrid');
        const serviceSelectionSection = document.getElementById('serviceSelectionSection');
        const serviceListGrid = document.getElementById('serviceListGrid');
        const serviceInput = document.getElementById('serviceTypeInput');

        const hospitalSection = document.getElementById('hospitalSection');
        const hospitalGrid = document.getElementById('hospitalGrid');
        const step2 = document.getElementById('step2');
        const dateInput = document.getElementById('dateInput');
        const timeSlotContainer = document.getElementById('timeSlotContainer');
        const timeInput = document.getElementById('timeInput');
        const hospitalIdInput = document.getElementById('hospitalIdInput');
        const priceInput = document.getElementById('priceInput');
        const totalPriceDisplay = document.getElementById('totalPriceDisplay');
        const selectedClinicName = document.getElementById('selectedClinicName');
        const stepBadge = document.getElementById('stepBadge');

        // State
        let currentCategory = null;
        let currentService = null;
        let currentHospitalId = null;

        // Initialize: Fetch Categories
        async function fetchCategories() {
            try {
                const res = await fetch('api/get_service_categories.php');
                const result = await res.json();

                if (result.success) {
                    renderCategories(result.data);
                } else {
                    categoryGrid.innerHTML = 'Error loading categories';
                }
            } catch (e) {
                console.error(e);
                categoryGrid.innerHTML = 'Failed to load categories';
            }
        }

        // Render Categories
        function renderCategories(categories) {
            categoryGrid.innerHTML = '';
            categories.forEach(cat => {
                const div = document.createElement('div');
                div.className = 'service-option';
                div.innerHTML = `
                    <i class="fa-solid ${cat.icon || 'fa-paw'}"></i>
                    <span class="service-name">${cat.name}</span>
                `;
                div.addEventListener('click', () => {
                    // Highlight logic
                    document.querySelectorAll('#categoryGrid .service-option').forEach(el => el.classList.remove('active'));
                    div.classList.add('active');

                    selectCategory(cat.id);
                });
                categoryGrid.appendChild(div);
            });
        }

        // Handle Category Selection
        function selectCategory(categoryId) {
            currentCategory = categoryId;
            serviceSelectionSection.style.display = 'block';
            serviceListGrid.innerHTML = '<div style="grid-column:1/-1; text-align:center;">Loading services...</div>';

            // Reset downstream
            resetServiceSelection();
            resetHospitalSelection();

            fetchServices(categoryId);
        }

        // Fetch Services by Category
        async function fetchServices(categoryId) {
            try {
                const res = await fetch(`api/get_services.php?category_id=${categoryId}`);
                const result = await res.json();

                if (result.success) {
                    renderServices(result.data);
                } else {
                    serviceListGrid.innerHTML = 'Error loading services';
                }
            } catch (e) {
                console.error(e);
                serviceListGrid.innerHTML = 'Failed to load services';
            }
        }

        // Render Services
        function renderServices(services) {
            serviceListGrid.innerHTML = '';
            if (services.length === 0) {
                serviceListGrid.innerHTML = '<div style="grid-column:1/-1; text-align:center;">No services found for this category.</div>';
                return;
            }

            services.forEach(srv => {
                const div = document.createElement('div');
                div.className = 'service-option';
                // Smaller padding for list items
                div.style.padding = '1rem';
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.gap = '0.75rem';

                div.innerHTML = `
                    <div style="font-weight:600; font-size:0.9rem;">${srv.name}</div>
                    <div style="margin-left:auto; font-size:0.75rem; color:#64748b;">${srv.default_duration_minutes}m</div>
                `;

                div.addEventListener('click', () => {
                    document.querySelectorAll('#serviceListGrid .service-option').forEach(el => el.classList.remove('active'));
                    div.classList.add('active');

                    selectService(srv.name);
                });
                serviceListGrid.appendChild(div);
            });
        }

        function selectService(serviceName) {
            currentService = serviceName;
            serviceInput.value = serviceName;

            resetHospitalSelection();
            fetchHospitals(serviceName);
        }

        function resetServiceSelection() {
            currentService = null;
            serviceInput.value = '';
            hospitalSection.style.display = 'none';
        }

        // Start
        fetchCategories();

        async function fetchHospitals(service) {
            hospitalGrid.innerHTML = '<div style="text-align:center; color:#64748b;">Loading clinics...</div>';
            hospitalSection.style.display = 'block';

            try {
                const res = await fetch(`api_get_hospitals.php?service=${service}`);
                const data = await res.json();

                hospitalGrid.innerHTML = '';
                if (data.length === 0) {
                    hospitalGrid.innerHTML = '<div style="color:red;">No clinics found for this service.</div>';
                    return;
                }

                data.forEach(h => {
                    const card = document.createElement('div');
                    card.className = 'service-option'; // Reusing style for simplicity
                    card.style.display = 'flex';
                    card.style.alignItems = 'center';
                    card.style.gap = '1rem';
                    card.style.marginBottom = '0.5rem';
                    card.style.textAlign = 'left';

                    card.innerHTML = `
                        <img src="${h.image_url}" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                        <div style="flex:1;">
                            <div style="font-weight:700; color:#1e293b;">${h.name}</div>
                            <div style="font-size:0.8rem; color:#64748b;">${h.address}</div>
                        </div>
                        <div style="font-weight:700; color:#10b981;">₹${h.price}</div>
                    `;

                    card.addEventListener('click', () => {
                        // Highlight logic
                        document.querySelectorAll('#hospitalGrid > div').forEach(d => {
                            d.style.borderColor = '#e2e8f0';
                            d.style.background = 'white';
                        });
                        card.style.borderColor = '#3b82f6';
                        card.style.background = '#eff6ff';

                        selectHospital(h);
                    });

                    hospitalGrid.appendChild(card);
                });

            } catch (e) {
                console.error(e);
                hospitalGrid.innerHTML = 'Error loading clinics.';
            }
        }

        function selectHospital(h) {
            currentHospitalId = h.id;
            hospitalIdInput.value = h.id;
            priceInput.value = h.price;

            // Update UI
            totalPriceDisplay.textContent = '₹' + h.price;
            selectedClinicName.textContent = h.name;

            // Show next step
            step2.style.display = 'block';
            stepBadge.innerHTML = '<div class="step-dot"></div> Step 2: Time';

            // Trigger slot fetch if date already present
            if (dateInput.value) fetchSlots();
        }

        function resetHospitalSelection() {
            currentHospitalId = null;
            hospitalIdInput.value = '';
            hospitalGrid.innerHTML = '';
            step2.style.display = 'none';
            totalPriceDisplay.textContent = '₹0';
        }

        // 2. Date Selection -> Fetch Slots
        dateInput.addEventListener('change', fetchSlots);

        async function fetchSlots() {
            if (!currentHospitalId || !dateInput.value) return;

            timeSlotContainer.innerHTML = 'Loading...';

            try {
                const res = await fetch(`api_get_slots.php?hospital_id=${currentHospitalId}&date=${dateInput.value}`);
                const slots = await res.json();

                timeSlotContainer.innerHTML = '';

                if (slots.length === 0) {
                    timeSlotContainer.innerHTML = 'No slots available.';
                    return;
                }

                slots.forEach(slot => {
                    const div = document.createElement('div');
                    div.className = `time-slot ${slot.available ? '' : 'disabled'}`;
                    div.textContent = slot.display;

                    if (slot.available) {
                        div.addEventListener('click', () => {
                            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('active'));
                            div.classList.add('active');
                            timeInput.value = slot.time;
                        });
                    }

                    timeSlotContainer.appendChild(div);
                });

            } catch (e) {
                console.error(e);
            }
        }

    </script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.getElementById('btnContinue').onclick = function (e) {
            const form = document.getElementById('bookingForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const amount = parseInt(priceInput.value) * 100;
            if (isNaN(amount) || amount <= 0) {
                alert('Please select a service and clinic first.');
                return;
            }

            if ("<?php echo RAZORPAY_KEY_ID; ?>".indexOf('xxxx') !== -1) {
                alert('Razorpay API Key not configured in config.php');
                return;
            }

            var options = {
                "key": "<?php echo RAZORPAY_KEY_ID; ?>",
                "amount": amount,
                "currency": "INR",
                "name": "PetCloud",
                "description": "Appointment for " + (document.getElementById('petNameInput').value || 'Pet'),
                "image": "https://img.icons8.com/deco/600/dog.png",
                "handler": function (response) {
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    form.submit();
                },
                "prefill": {
                    "name": "<?php echo htmlspecialchars($user_name); ?>",
                    "email": "<?php echo $_SESSION['user_email'] ?? ''; ?>",
                    "contact": ""
                },
                "theme": { "color": "#3b82f6" }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
            e.preventDefault();
        }
    </script>
</body>

</html>