<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$listing_id = $input['listing_id'] ?? $_POST['listing_id'] ?? null;
$user_id = $input['user_id'] ?? $_POST['user_id'] ?? null;

if (!$listing_id || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Missing listing_id or user_id']);
    exit();
}

try {
    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM adoption_listings WHERE id = ? AND user_id = ?");
    $check->execute([$listing_id, $user_id]);

    if ($check->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM adoption_listings WHERE id = ?");
        $stmt->execute([$listing_id]);

        echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Permission denied or listing not found']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>