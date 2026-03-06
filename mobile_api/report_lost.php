<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['pet_id']) || !isset($input['location'])) {
    echo json_encode(['success' => false, 'error' => 'Missing Fields']);
    exit;
}

$user_id = $input['user_id'];
$pet_id = $input['pet_id'];
$location = $input['location'];
$date = $input['date'] ?? date('Y-m-d');
$description = $input['description'] ?? 'Lost pet reported via mobile app.';

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
    $stmt = $pdo->prepare("INSERT INTO lost_pet_alerts (pet_id, user_id, last_seen_location, last_seen_date, description, status) VALUES (?, ?, ?, ?, ?, 'Active')");
    $stmt->execute([$pet_id, $user_id, $location, $date, $description]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>