<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['shipping_details'])) {
    echo json_encode(['success' => false, 'error' => 'Missing Fields']);
    exit;
}

$user_id = $input['user_id'];
$shipping = $input['shipping_details'];
$total_amount = $input['total_amount'] ?? 0;

try {
    $pdo->beginTransaction();

    // 1. Fetch Cart Items
    $stmt = $pdo->prepare("SELECT c.product_id, c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        throw new Exception("Your cart is empty.");
    }

    // Recalculate total if not passed or to be safe
    $calculated_total = 0;
    foreach ($items as $item) {
        $calculated_total += $item['price'] * $item['quantity'];
    }

    // 2. Insert into Orders
    $address = $shipping['address'] . ", " . $shipping['city'] . ", " . $shipping['zip'];
    $payment_id = $input['payment_id'] ?? 'SIMULATED_' . time();
    $payment_method = $input['payment_method'] ?? 'Card';

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, payment_id, payment_method, total_amount, shipping_address, city, zip_code, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Processing')");
    $stmt->execute([$user_id, $payment_id, $payment_method, $calculated_total, $address, $shipping['city'], $shipping['zip']]);
    $order_id = $pdo->lastInsertId();

    // 3. Insert into Order Items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // 4. Clear Cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>