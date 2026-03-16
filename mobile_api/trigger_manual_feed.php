<?php
error_reporting(0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../api/db.php";

// Use application/x-www-form-urlencoded if needed (simpler for basic fetch)
$user_id = intval($_POST["user_id"] ?? 0);
$pet_id = intval($_POST["pet_id"] ?? 0);
$qty = intval($_POST["quantity"] ?? 30);
$device_id = "esp32_1";

if (empty($user_id) || empty($pet_id)) {
    echo json_encode(["ok" => false, "error" => "User ID and Pet ID are required"]);
    exit;
}

// Transaction style inserts
$conn->begin_transaction();

try {
    // 1) Update dashboard "Recent Activity" (feeding_logs)
    $stmt1 = $conn->prepare("INSERT INTO feeding_logs (user_id, pet_id, quantity_grams, status, message) VALUES (?, ?, ?, 'Success', 'Manual feeding triggered from mobile app')");
    if (!$stmt1)
        throw new Exception("Failed to prepare feeding_logs stmt");
    $stmt1->bind_param("iii", $user_id, $pet_id, $qty);
    $stmt1->execute();

    // 2) Send command to ESP32 (feed_commands)
    $stmt2 = $conn->prepare("INSERT INTO feed_commands (device_id, portion_qty, status) VALUES (?, ?, 'pending')");
    if ($stmt2) {
        $stmt2->bind_param("si", $device_id, $qty);
        $stmt2->execute();
    }

    // 3) Update "Last fed" display immediately (feed_logs)
    $stmt3 = $conn->prepare("INSERT INTO feed_logs (device_id, `portion`) VALUES (?, ?)");
    if ($stmt3) {
        $stmt3->bind_param("si", $device_id, $qty);
        $stmt3->execute();
    }

    $conn->commit();
    echo json_encode(["ok" => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["ok" => false, "error" => "Database error during feeding: " . $e->getMessage()]);
}
?>