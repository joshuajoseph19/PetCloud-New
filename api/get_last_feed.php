<?php
header("Content-Type: application/json");
require_once "db.php";

$device_id = $_GET["device_id"] ?? "";
if (empty($device_id)) {
    echo json_encode(["ok" => false, "error" => "missing device_id"]);
    exit;
}

$stmt = $conn->prepare("SELECT fed_at, `portion` FROM feed_logs WHERE device_id=? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $device_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    echo json_encode([
        "ok" => true,
        "fed_at" => date('M d, g:i A', strtotime($row['fed_at'])),
        "portion" => $row['portion']
    ]);
} else {
    echo json_encode([
        "ok" => true,
        "fed_at" => null,
        "portion" => null
    ]);
}
