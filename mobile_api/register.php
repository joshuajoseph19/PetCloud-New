<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$full_name = trim($input['full_name']);
$email = trim($input['email']);
$password = $input['password'];

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['error' => 'Email is already registered']);
        exit();
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user with role 'client'
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'client')");
    $stmt->execute([$full_name, $email, $password_hash]);
    
    $userId = $pdo->lastInsertId();
    
    // Return the new user object (excluding hash)
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful',
        'user' => [
            'id' => $userId,
            'full_name' => $full_name,
            'email' => $email,
            'role' => 'client'
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
