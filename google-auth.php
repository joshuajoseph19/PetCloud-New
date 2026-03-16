<?php
session_start();
require 'db_connect.php';

// Receive JSON Post
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['email'])) {
    $email = $data['email'];
    $name = $data['name'];
    $picture = $data['picture'];

    // Check if user exists in DB, if not create them
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'] ?? 'client';
    } else {
        // Create new Google User
        // Note: Password usage for Google users is tricky. We set a random null/unusable one or handle it in DB schema.
        // My schema has password_hash nullable? No, I defined it as varchar.
        // I will insert a dummy hash that can never be matched.
        $dummy_hash = '$2y$10$GOOGLEAUTHUSERRANDOMSTRING';

        $sql = "INSERT INTO users (full_name, email, password, profile_pic, role) VALUES (?, ?, ?, ?, 'client')";
        $pdo->prepare($sql)->execute([$name, $email, $dummy_hash, $picture]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['role'] = 'client';
    }

    // Set Session Variables
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['profile_pic'] = $picture;

    echo json_encode(['status' => 'success', 'role' => $_SESSION['role']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>