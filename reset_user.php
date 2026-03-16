<?php
require_once 'db_connect.php';
try {
    $email = 'joshuajoseph10310@gmail.com';
    $password = 'admin';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);

    if ($stmt->rowCount() > 0) {
        echo "Password for $email reset to 'admin'.\n";
    } else {
        echo "User $email not found or password already 'admin'.\n";
        // Let's check if user exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if (!$check->fetch()) {
            // Create it
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES ('Joshua Joseph', ?, ?, 'client')");
            $stmt->execute([$email, $hash]);
            echo "User $email created with password 'admin'.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
