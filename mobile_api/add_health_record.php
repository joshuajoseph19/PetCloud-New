<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user_id = $data['user_id'] ?? null;
$pet_id = $data['pet_id'] ?? null;
$record_type = $data['record_type'] ?? null;
$date = $data['date'] ?? null;
$title = $data['title'] ?? null;
$description = $data['description'] ?? '';

if (!$user_id || !$pet_id || !$record_type || !$date || !$title) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO health_records (user_id, pet_id, record_type, record_date, title, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $pet_id, $record_type, $date, $title, $description]);

    echo json_encode(['success' => true, 'message' => 'Health record added successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
