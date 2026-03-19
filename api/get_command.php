<?php
header("Content-Type: application/json");
require_once "db.php";

$device_id = $_GET["device_id"] ?? "esp32_1";

// Only pick up commands inserted within the last 2 minutes.
// This prevents stale 'pending' rows from auto-triggering the
// motor whenever the ESP32 reboots or reconnects to WiFi.
$q = $conn->prepare(
  "SELECT id, portion_qty FROM feed_commands
   WHERE device_id = ?
     AND status = 'pending'
     AND created_at >= NOW() - INTERVAL 2 MINUTE
   ORDER BY id ASC LIMIT 1"
);
$q->bind_param("s", $device_id);
$q->execute();
$res = $q->get_result();

if ($row = $res->fetch_assoc()) {
  echo json_encode([
    "ok"      => true,
    "id"      => $row["id"],
    "portion" => $row["portion_qty"]
  ]);
} else {
  echo json_encode(["ok" => false]);
}