<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

// Allow fetching via POST (with user_id) 
$user_id = $input['user_id'] ?? $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'Missing user_id']);
    exit();
}

try {
    // 1. User Info
    $stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Greeting logic
    date_default_timezone_set('Asia/Kolkata');
    $hour = date('H');
    if ($hour >= 5 && $hour < 12)
        $greeting = "Good Morning";
    elseif ($hour >= 12 && $hour < 17)
        $greeting = "Good Afternoon";
    elseif ($hour >= 17 && $hour < 21)
        $greeting = "Good Evening";
    else
        $greeting = "Good Night";

    // 3. User Pets
    $stmt = $pdo->prepare("SELECT id, pet_name, pet_breed, pet_image, status FROM user_pets WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Feeding Schedules (Today)
    $stmt = $pdo->prepare("
        SELECT fs.*, p.pet_name 
        FROM feeding_schedules fs 
        LEFT JOIN user_pets p ON fs.pet_id = p.id 
        WHERE fs.user_id = ? 
        ORDER BY fs.feeding_time ASC
    ");
    $stmt->execute([$user_id]);
    $allSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $todayDay = date('D');
    $feedingSchedules = [];
    foreach ($allSchedules as $fs) {
        $days = json_decode($fs['days_of_week'] ?? '[]');
        if (!is_array($days) || empty($days) || in_array($todayDay, $days)) {
            $feedingSchedules[] = $fs;
        }
    }

    // 5. Upcoming Appointments
    $stmt = $pdo->prepare("
        SELECT a.*, h.name as hospital_name
        FROM appointments a 
        LEFT JOIN hospitals h ON a.hospital_id = h.id 
        WHERE a.user_id = ? AND a.status != 'cancelled' 
        AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, a.appointment_time ASC 
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Health Reminders (Pending)
    $stmt = $pdo->prepare("SELECT id, title, due_at, status FROM health_reminders WHERE user_id = ? AND status = 'pending' ORDER BY due_at ASC LIMIT 3");
    $stmt->execute([$user_id]);
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Nearby Lost Pets & Strays
    $stmt = $pdo->prepare("SELECT location FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userLoc = $stmt->fetchColumn() ?? '';
    $city = trim(explode(',', $userLoc)[0]);

    $nearbyLostPets = [];
    $nearbyStrays = [];
    if ($city) {
        $stmt = $pdo->prepare("
            SELECT lpa.*, p.pet_name, p.pet_breed, p.pet_image
            FROM lost_pet_alerts lpa
            JOIN user_pets p ON lpa.pet_id = p.id
            WHERE lpa.status = 'Active' 
            AND (lpa.last_seen_location LIKE ? OR ? LIKE CONCAT('%', lpa.last_seen_location, '%'))
            ORDER BY lpa.created_at DESC LIMIT 5
        ");
        $stmt->execute(["%$city%", $userLoc]);
        $nearbyLostPets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT * FROM general_found_pets 
            WHERE status = 'Active' 
            AND (found_location LIKE ? OR ? LIKE CONCAT('%', found_location, '%'))
            ORDER BY created_at DESC LIMIT 5
        ");
        $stmt->execute(["%$city%", $userLoc]);
        $nearbyStrays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Daily Tasks
    $stmt = $pdo->prepare("SELECT * FROM daily_tasks WHERE user_id = ? AND task_date = CURDATE()");
    $stmt->execute([$user_id]);
    $dailyTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 10. Found Pet Reports (Sightings of USER'S lost pets)
    $stmt = $pdo->prepare("
        SELECT fr.*, p.pet_name, p.pet_image, u.full_name as reporter_name, lpa.pet_id
        FROM found_pet_reports fr
        JOIN lost_pet_alerts lpa ON fr.alert_id = lpa.id
        JOIN user_pets p ON lpa.pet_id = p.id
        JOIN users u ON fr.user_id = u.id
        WHERE lpa.user_id = ? AND lpa.status = 'Active'
        ORDER BY fr.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $lostPetReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 11. My Orders (Recent)
    $stmt = $pdo->prepare("
        SELECT o.id, o.user_id, o.payment_id, o.total_amount, o.shipping_address, o.city, o.zip_code, o.status, o.created_at AS order_date, o.payment_method,
               (SELECT p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as product_image,
               (SELECT p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as product_name
        FROM orders o
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter to ensure working images for demo
    $workingImages = [
        'Bird Seed Mix' => 'images/bird_feed.webp',
        'Chew Bone' => 'images/chew_bone.jpg',
        'Pet Vitamin Supplements' => 'images/Pet Vitamin Supplements.webp',
        'Comfort Pet Bed' => 'images/Comfort Pet Bed.webp',
        'Interactive Cat Toy' => 'images/cat_toy.jpg',
        'Premium Dog Food' => 'images/premium_dog_food.webp',
        'Puppy Food' => 'images/dog_food.jpg'
    ];

    foreach ($orders as &$o) {
        if (isset($o['product_name']) && isset($workingImages[$o['product_name']])) {
            $o['product_image'] = $workingImages[$o['product_name']];
        }
    }

    echo json_encode([
        'success' => true,
        'user' => $user,
        'greeting' => $greeting,
        'pets' => $pets,
        'feeding_schedules' => $feedingSchedules,
        'appointments' => $appointments,
        'reminders' => $reminders,
        'nearbyLostPets' => $nearbyLostPets,
        'nearbyStrays' => $nearbyStrays,
        'dailyTasks' => $dailyTasks,
        'orders' => $orders,
        'lostPetReports' => $lostPetReports
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>