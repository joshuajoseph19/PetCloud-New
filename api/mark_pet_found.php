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

if (!$pet_id) {
    echo json_encode(['success' => false, 'message' => 'Missing pet ID']);
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

    // 2. Update pet status to Active
    $stmt = $pdo->prepare("UPDATE user_pets SET status = 'Active' WHERE id = ?");
    $stmt->execute([$pet_id]);

    // 3. Resolve all active alerts for this pet
    $stmt = $pdo->prepare("UPDATE lost_pet_alerts SET status = 'Resolved' WHERE pet_id = ? AND status = 'Active'");
    $stmt->execute([$pet_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Welcome home! Pet marked as found.']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>