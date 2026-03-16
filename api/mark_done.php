<?php
header("Content-Type: application/json");
require_once "db.php";

$id = intval($_POST["id"] ?? 0);
if ($id <= 0) {
    echo json_encode(["ok" => false, "error" => "bad id"]);
    exit;
}

$stmt = $conn->prepare("UPDATE feed_commands SET status='done' WHERE id=?");
$stmt->bind_param("i", $id);
$success = $stmt->execute();

if ($success) {
    // Fetch device_id and portion
    $sel = $conn->prepare("SELECT device_id, portion_qty FROM feed_commands WHERE id=?");
    $sel->bind_param("i", $id);
    $sel->execute();
    $res = $sel->get_result();
    if ($row = $res->fetch_assoc()) {
        $ins = $conn->prepare("INSERT INTO feed_logs (device_id, `portion`) VALUES (?, ?)");
        $ins->bind_param("si", $row['device_id'], $row['portion_qty']);
        $ins->execute();
    }
}

echo json_encode(["ok" => $success]);