<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$pet_id = $input['pet_id'] ?? null;
$user_id = $input['user_id'] ?? null;

if (!$pet_id || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Missing pet_id or user_id']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM user_pets WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$pet_id, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete pet']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>