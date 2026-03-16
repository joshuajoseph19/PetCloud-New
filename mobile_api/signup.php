<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password']) || !isset($input['name'])) {
    echo json_encode(['error' => 'Missing fields']);
    exit();
}

$email = $input['email'];
$password = $input['password'];
$name = $input['name'];

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['error' => 'Email already registered']);
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'client')");
    $stmt->execute([$name, $email, $hash]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>