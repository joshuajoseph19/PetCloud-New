<?php
/**
 * Device Online/Offline Status Endpoint
 * 
 * Checks the device_heartbeats table to determine if a device is online.
 * A device is considered ONLINE if its last ping was within 60 seconds.
 * 
 * Usage: GET /api/device_status.php?device_id=esp32_1
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

$device_id = $_GET['device_id'] ?? $_POST['device_id'] ?? 'esp32_1';
$device_id = trim($device_id);

// Auto-create table if missing (safe guard)
$conn->query("CREATE TABLE IF NOT EXISTS device_heartbeats (
    device_id  VARCHAR(50) NOT NULL PRIMARY KEY,
    last_seen  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare(
    "SELECT last_seen, TIMESTAMPDIFF(SECOND, last_seen, NOW()) AS seconds_ago
     FROM device_heartbeats
     WHERE device_id = ?
     LIMIT 1"
);
$stmt->bind_param("s", $device_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $seconds_ago = (int)$row['seconds_ago'];
    // Online if pinged within last 60 seconds
    $is_online = $seconds_ago <= 60;
    echo json_encode([
        "ok"          => true,
        "status"      => $is_online ? "Online" : "Offline",
        "last_seen"   => $row['last_seen'],
        "seconds_ago" => $seconds_ago
    ]);
} else {
    // No heartbeat record at all = never seen = Offline
    echo json_encode([
        "ok"          => true,
        "status"      => "Offline",
        "last_seen"   => null,
        "seconds_ago" => null
    ]);
}
?>
