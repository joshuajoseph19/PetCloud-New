<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once 'db_connect.php';

$hospital_id = $_GET['hospital_id'] ?? 0;
$date = $_GET['date'] ?? '';

if (!$hospital_id || !$date) {
    echo json_encode([]);
    exit;
}

// Define working hours (e.g., 09:00 to 18:00)
$start_hour = 9;
$end_hour = 17; // Last slot at 17:00 (5 PM)
$interval_minutes = 60; // 1 hour slots

$all_slots = [];
for ($h = $start_hour; $h <= $end_hour; $h++) {
    $time_str = sprintf('%02d:00:00', $h);
    $display_time = date('h:i A', strtotime($time_str));

    // Logic for past times if date is today
    if ($date == date('Y-m-d') && $h <= date('H')) {
        // Skip past hours
        continue;
    }

    $all_slots[] = [
        'time' => $time_str,
        'display' => $display_time,
        'available' => true
    ];
}

try {
    // Fetch booked slots
    $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE hospital_id = ? AND appointment_date = ? AND status != 'cancelled'");
    $stmt->execute([$hospital_id, $date]);
    $booked_times = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Mark unavailable
    foreach ($all_slots as &$slot) {
        if (in_array($slot['time'], $booked_times)) {
            $slot['available'] = false;
        }
    }

    echo json_encode(array_values($all_slots));
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>