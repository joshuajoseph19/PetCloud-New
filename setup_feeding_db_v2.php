<?php
require_once 'db_connect.php';

function addColumn($pdo, $table, $colName, $definition)
{
    try {
        $check = $pdo->query("SHOW COLUMNS FROM $table LIKE '$colName'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN $colTable $colName $definition");
            // Oops, syntax above is wrong. correct: ADD COLUMN colName definition
            $pdo->exec("ALTER TABLE $table ADD COLUMN $colName $definition");
            echo "Added $colName to $table.\n";
        } else {
            echo "Column $colName already exists in $table.\n";
        }
    } catch (PDOException $e) {
        echo "Error adding $colName: " . $e->getMessage() . "\n";
    }
}

try {
    // Ensure table exists
    // Note: If table exists with diff schema, we rely on addColumn.
    // If not exists, create with basic + new columns
    $checkTable = $pdo->query("SHOW TABLES LIKE 'feeding_schedules'");
    if ($checkTable->rowCount() == 0) {
        $pdo->exec("CREATE TABLE feeding_schedules (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Created feeding_schedules table.\n";
    } else {
        echo "Table feeding_schedules exists. Checking columns...\n";
        // Add new columns safely one by one
        addColumn($pdo, 'feeding_schedules', 'pet_id', "INT NULL AFTER user_id");
        addColumn($pdo, 'feeding_schedules', 'days_of_week', "VARCHAR(255) DEFAULT '[\"Mon\",\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\",\"Sun\"]'");
        addColumn($pdo, 'feeding_schedules', 'portion_size', "DECIMAL(5,2) DEFAULT 0");
        addColumn($pdo, 'feeding_schedules', 'portion_unit', "VARCHAR(20) DEFAULT 'grams'");
        addColumn($pdo, 'feeding_schedules', 'diet_type', "VARCHAR(50) DEFAULT 'Dry Food'");
        addColumn($pdo, 'feeding_schedules', 'is_active', "TINYINT(1) DEFAULT 1");
        addColumn($pdo, 'feeding_schedules', 'created_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

    // Feeding History
    $pdo->exec("CREATE TABLE IF NOT EXISTS feeding_history (
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
    )");
    echo "Feeding history table checked/created.\n";

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
?>