<?php
header("Content-Type: application/json");
require_once "db.php";

$device_id = $_POST["device_id"] ?? "esp32_1";
$portion   = intval($_POST["portion"] ?? 50);

$stmt = $conn->prepare(
  "INSERT INTO feed_commands (device_id, portion_qty, status)
   VALUES (?, ?, 'pending')"
);
$stmt->bind_param("si", $device_id, $portion);

if ($stmt->execute()) {
  echo json_encode(["ok" => true]);
} else {
  echo json_encode(["ok" => false]);
}