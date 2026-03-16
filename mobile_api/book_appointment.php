<?php
error_reporting(0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['hospital_id']) || !isset($input['appointment_date'])) {
    echo json_encode(['success' => false, 'error' => 'Missing Fields']);
    exit;
}

$user_id = $input['user_id'];
$hospital_id = $input['hospital_id'];
$date = $input['appointment_date'];
$time = $input['appointment_time'];
$pet_name = $input['pet_name'] ?? 'Pet';
$breed = $input['breed'] ?? 'Unknown';
$service_type = $input['service_type'] ?? 'General';
$payment_id = $input['payment_id'] ?? ('MOB_' . time());
$payment_method = $input['payment_method'] ?? 'Card';
$cost = $input['cost'] ?? 0;

try {
    $stmt = $pdo->prepare("
        INSERT INTO appointments 
        (user_id, payment_id, payment_method, hospital_id, pet_name, breed, service_type, title, appointment_date, appointment_time, description, cost, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ");

    $title = $service_type . " for " . $pet_name;
    $description = "Booked via Mobile App";

    $stmt->execute([
        $user_id,
        $payment_id,
        $payment_method,
        $hospital_id,
        $pet_name,
        $breed,
        $service_type,
        $title,
        $date,
        $time,
        $description,
        $cost
    ]);

    echo json_encode(['success' => true, 'appointment_id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>