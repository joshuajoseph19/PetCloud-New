<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../api/db.php";

$user_id = $_POST["user_id"] ?? "";
$pet_id = $_POST["pet_id"] ?? "";
$time = $_POST["feeding_time"] ?? "";
$qty = intval($_POST["quantity"] ?? 40);

if (empty($user_id) || empty($pet_id) || empty($time)) {
    echo json_encode(["ok" => false, "error" => "User ID, Pet ID, and Feeding Time are required"]);
    exit;
}

// 1) Simplified: Weekly/Daily default to Daily for minimal version
$stmt = $conn->prepare("INSERT INTO smart_feeder_schedules (user_id, pet_id, feeding_time, quantity_grams, mode, frequency) VALUES (?, ?, ?, ?, 'Manual', 'Daily')");
$stmt->bind_param("iisi", $user_id, $pet_id, $time, $qty);

if ($stmt->execute()) {
    echo json_encode(["ok" => true, "message" => "Schedule saved"]);
} else {
    echo json_encode(["ok" => false, "error" => "Database error saving schedule: " . $conn->error]);
}
?>