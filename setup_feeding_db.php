<?php
require_once 'db_connect.php';

try {
    // 1. Alter feeding_schedules table or recreate it
    // Check if table exists
    $check = $pdo->query("SHOW TABLES LIKE 'feeding_schedules'");
    if ($check->rowCount() > 0) {
        // Table exists, check for new columns
        $cols = $pdo->query("SHOW COLUMNS FROM feeding_schedules LIKE 'pet_id'");
        if ($cols->rowCount() == 0) {
            // Add columns
            $sql = "ALTER TABLE feeding_schedules
                    ADD COLUMN pet_id INT NULL AFTER user_id,
                    ADD COLUMN days_of_week VARCHAR(255) DEFAULT '[\"Mon\",\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\",\"Sun\"]',
                    ADD COLUMN portion_size DECIMAL(5,2) DEFAULT 0,
                    ADD COLUMN portion_unit VARCHAR(20) DEFAULT 'grams',
                    ADD COLUMN diet_type VARCHAR(50) DEFAULT 'Dry Food',
                    ADD COLUMN is_active TINYINT(1) DEFAULT 1,
                    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $pdo->exec($sql);
            echo "Updated feeding_schedules table.<br>";
        } else {
            echo "feeding_schedules table already up to date.<br>";
        }
    } else {
        // Create table
        $sql = "CREATE TABLE feeding_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            pet_id INT NULL,
            meal_name VARCHAR(100) NOT NULL,
            food_description VARCHAR(255),
            feeding_time TIME NOT NULL,
            days_of_week VARCHAR(255) DEFAULT '[\"Mon\",\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\",\"Sun\"]',
            portion_size DECIMAL(5,2) DEFAULT 0,
            portion_unit VARCHAR(20) DEFAULT 'grams',
            diet_type VARCHAR(50) DEFAULT 'Dry Food',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "Created feeding_schedules table.<br>";
    }

    // 2. Create feeding_history table
    $sql = "CREATE TABLE IF NOT EXISTS feeding_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        schedule_id INT NULL,
        pet_id INT NOT NULL,
        user_id INT NOT NULL,
        fed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('completed', 'missed', 'skipped') DEFAULT 'completed',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (schedule_id) REFERENCES feeding_schedules(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "Created feeding_history table.<br>";

    // 3. Sample Data (Optional)
    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>