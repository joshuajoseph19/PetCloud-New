<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;
$pet_id = $input['pet_id'] ?? null;

if (!$user_id || !$pet_id) {
    echo json_encode(['success' => false, 'error' => 'Missing user_id or pet_id']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Re-activate the pet
    $stmt = $pdo->prepare("UPDATE user_pets SET status = 'Active' WHERE id = ? AND user_id = ?");
    $stmt->execute([$pet_id, $user_id]);

    // 2. Resolve the lost pet alert
    $stmt = $pdo->prepare("UPDATE lost_pet_alerts SET status = 'Resolved' WHERE pet_id = ? AND status = 'Active'");
    $stmt->execute([$pet_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Pet marked as safely found!']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>