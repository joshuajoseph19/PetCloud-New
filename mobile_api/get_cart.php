<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'Missing User ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT c.id AS cart_id, c.product_id, c.quantity, p.name, p.price, p.image_url, p.category 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter to ensure working images for demo (same logic as get_marketplace.php)
    $workingImages = [
        'Bird Seed Mix' => 'images/bird_feed.webp',
        'Chew Bone' => 'images/chew_bone.jpg',
        'Pet Vitamin Supplements' => 'images/Pet Vitamin Supplements.webp',
        'Comfort Pet Bed' => 'images/Comfort Pet Bed.webp',
        'Interactive Cat Toy' => 'images/cat_toy.jpg',
        'Premium Dog Food' => 'images/premium_dog_food.webp',
        'Puppy Food' => 'images/puppy_food.avif'
    ];

    foreach ($items as &$item) {
        if (isset($workingImages[$item['name']])) {
            $item['image_url'] = $workingImages[$item['name']];
        }
    }

    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    echo json_encode(['success' => true, 'data' => $items, 'total' => $total]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>