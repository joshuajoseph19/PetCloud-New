<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Missing user_id']);
    exit();
}

try {
    // Fetch user pets first to get records for them
    $stmt = $pdo->prepare("SELECT id, pet_name FROM user_pets WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($pets as $pet) {
        $stmt = $pdo->prepare("SELECT * FROM health_records WHERE pet_id = ? ORDER BY record_date DESC");
        $stmt->execute([$pet['id']]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($records)) {
            $results[] = [
                'pet_name' => $pet['pet_name'],
                'records' => $records
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $results]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>