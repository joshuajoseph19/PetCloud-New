<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['product_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing Fields']);
    exit;
}

$user_id = $input['user_id'];
$product_id = $input['product_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>