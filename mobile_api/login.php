<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['error' => 'Missing email or password']);
    exit();
}

$email = $input['email'];
$password = $input['password'];

try {
    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['error' => 'Invalid credentials']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>