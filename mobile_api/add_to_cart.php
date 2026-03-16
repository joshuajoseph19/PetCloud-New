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
    // Check if item exists in cart
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$existing['id']]);
        echo json_encode(['success' => true, 'message' => 'Quantity incremented']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>