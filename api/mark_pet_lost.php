<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$pet_id = $_POST['pet_id'] ?? null;
$last_seen_location = $_POST['last_seen_location'] ?? '';
$last_seen_date = $_POST['last_seen_date'] ?? date('Y-m-d');
$description = $_POST['description'] ?? '';

if (!$pet_id || !$last_seen_location) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM user_pets WHERE id = ? AND user_id = ?");
    $stmt->execute([$pet_id, $user_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Pet not found or unauthorized");
    }

    // 2. Update pet status
    $stmt = $pdo->prepare("UPDATE user_pets SET status = 'Lost' WHERE id = ?");
    $stmt->execute([$pet_id]);

    // 3. Create alert
    $stmt = $pdo->prepare("INSERT INTO lost_pet_alerts (pet_id, user_id, last_seen_location, last_seen_date, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$pet_id, $user_id, $last_seen_location, $last_seen_date, $description]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Pet marked as lost successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>