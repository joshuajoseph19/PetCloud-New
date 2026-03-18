<?php
/**
 * ESP32 Heartbeat Ping Endpoint
 * 
 * The ESP32 calls this endpoint every 30 seconds to report it is alive.
 * Usage (HTTP POST from ESP32 firmware):
 *   POST https://yoursite.com/api/device_ping.php
 *   Body: device_id=esp32_1
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "db.php";

$device_id = $_POST['device_id'] ?? $_GET['device_id'] ?? 'esp32_1';
$device_id = trim($device_id);

if (empty($device_id)) {
    echo json_encode(["ok" => false, "error" => "device_id is required"]);
    exit;
}

// Auto-create table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS device_heartbeats (
    device_id  VARCHAR(50) NOT NULL PRIMARY KEY,
    last_seen  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Upsert: insert if new, update last_seen if existing
$stmt = $conn->prepare(
    "INSERT INTO device_heartbeats (device_id, last_seen)
     VALUES (?, NOW())
     ON DUPLICATE KEY UPDATE last_seen = NOW()"
);
$stmt->bind_param("s", $device_id);
$success = $stmt->execute();

echo json_encode([
    "ok"        => $success,
    "device_id" => $device_id,
    "time"      => date('Y-m-d H:i:s')
]);
?>
