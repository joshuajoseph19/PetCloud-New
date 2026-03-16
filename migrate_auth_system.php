<?php
require_once 'db_connect.php';

try {
    // 1. Create password_resets table
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (email),
        INDEX (token)
    )");
    echo "Table 'password_resets' created successfully.\n";

    // 2. Ensure users table has profile_pic column
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_pic'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL");
        echo "Column 'profile_pic' added to 'users' table.\n";
    } else {
        echo "Column 'profile_pic' already exists.\n";
    }

    echo "\nMigration complete!";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
