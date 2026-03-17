<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$category = $_GET['category'] ?? 'All';
$search = $_GET['search'] ?? '';

try {
    $sql = "SELECT p.* FROM products p WHERE 1=1";
    $params = [];

    if ($category !== 'All' && $category !== 'All Pets') {
        $sql .= " AND p.category = ?";
        $params[] = $category;
    }

    if (!empty($search)) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY p.id ASC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter to ensure working images for demo
    $workingImages = [
        'Bird Seed Mix' => 'images/bird_feed.webp',
        'Chew Bone' => 'images/chew_bone.jpg',
        'Pet Vitamin Supplements' => 'images/Pet Vitamin Supplements.webp',
        'Comfort Pet Bed' => 'images/Comfort Pet Bed.webp',
        'Interactive Cat Toy' => 'images/cat_toy.jpg',
        'Premium Dog Food' => 'images/premium_dog_food.webp',
        'Puppy Food' => 'images/dog_food.jpg'
    ];

    foreach ($products as &$p) {
        if (isset($workingImages[$p['name']])) {
            $p['image_url'] = $workingImages[$p['name']];
        }
    }

    echo json_encode(['success' => true, 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>