<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['pet_name'])) {
    echo json_encode(['success' => false, 'error' => 'Missing Fields']);
    exit;
}

$user_id = $input['user_id'];
$pet_name = $input['pet_name'];
$pet_breed = $input['pet_breed'] ?? 'Unknown';
$pet_image = $input['pet_image'] ?? 'uploads/pets/default.png';
$status = $input['status'] ?? 'Active';
$pet_gender = $input['pet_gender'] ?? 'Unknown';
$pet_age = $input['pet_age'] ?? '1 Year';
$pet_type = $input['pet_type'] ?? 'Other';
$pet_weight = $input['pet_weight'] ?? '0 kg';
$pet_description = $input['pet_description'] ?? '';

try {
    $stmt = $pdo->prepare("INSERT INTO user_pets (user_id, pet_name, pet_breed, pet_image, status, pet_gender, pet_age, pet_type, pet_weight, pet_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $pet_name, $pet_breed, $pet_image, $status, $pet_gender, $pet_age, $pet_type, $pet_weight, $pet_description]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>