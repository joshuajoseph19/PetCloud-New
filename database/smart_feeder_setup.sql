CREATE TABLE IF NOT EXISTS smart_feeder_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    feeding_time TIME NOT NULL,
    quantity_grams INT NOT NULL,
    mode ENUM('Automatic', 'Manual') DEFAULT 'Automatic',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES user_pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS feeding_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    feeding_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    quantity_grams INT NOT NULL,
    status ENUM('Success', 'Failed') DEFAULT 'Success',
    message VARCHAR(255) DEFAULT 'Feeding completed successfully',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES user_pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
