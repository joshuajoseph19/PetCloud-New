<?php
// error_reporting(0); // Prevent any warnings/notices from breaking JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../db_connect.php";

$user_id = intval($_GET["user_id"] ?? 0);

if (empty($user_id)) {
    echo json_encode(["ok" => false, "error" => "User ID is required"]);
    exit;
}

$response = ["ok" => true];

// 1. Get Last Feed Info
try {
    $stmt = $pdo->prepare("SELECT fed_at, `portion` FROM feed_logs WHERE device_id='esp32_1' ORDER BY id DESC LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $response["last_feed"] = [
                "time" => date('h:i A', strtotime($row['fed_at'])),
                "portion" => $row['portion'] . "g"
            ];
        } else {
            $response["last_feed"] = ["time" => "Not yet", "portion" => "--"];
        }
    } else {
        $response["last_feed"] = ["time" => "Not yet", "portion" => "--"];
    }
} catch (\Throwable $e) {
    $response["last_feed"] = ["time" => "Not yet", "portion" => "--"];
}

// 2. Get Recent Activity (last 5)
$history = [];
try {
    $historyStmt = $pdo->prepare("SELECT fl.id, fl.user_id, fl.pet_id, fl.feeding_time, fl.quantity_grams, fl.status, fl.message, up.pet_name FROM feeding_logs fl JOIN user_pets up ON fl.pet_id = up.id WHERE fl.user_id = ? ORDER BY fl.feeding_time DESC LIMIT 5");
    if ($historyStmt) {
        $historyStmt->execute([$user_id]);
        while ($row = $historyStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['time_formatted'] = date('M d, g:i A', strtotime($row['feeding_time']));
            $row['portion'] = $row['quantity_grams']; // Ensure frontend gets 'portion' if it expects it
            $history[] = $row;
        }
    }
} catch (\Throwable $e) {
}
$response["history"] = $history;

// 3. Get Active Schedules
$schedules = [];
try {
    $scheduleStmt = $pdo->prepare("SELECT s.id, s.user_id, s.pet_id, s.feeding_time, s.quantity_grams, s.mode, s.status, up.pet_name FROM smart_feeder_schedules s JOIN user_pets up ON s.pet_id = up.id WHERE s.user_id = ? AND s.status = 'Active' ORDER BY s.feeding_time ASC");
    if ($scheduleStmt) {
        $scheduleStmt->execute([$user_id]);
        while ($row = $scheduleStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['time_formatted'] = substr($row['feeding_time'], 0, 5);
            $row['portion'] = $row['quantity_grams'];
            $schedules[] = $row;
        }
    }
} catch (\Throwable $e) {
}
$response["schedules"] = $schedules;

echo json_encode($response);
?>