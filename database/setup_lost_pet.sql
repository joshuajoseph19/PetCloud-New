-- Add status to user_pets
ALTER TABLE user_pets ADD COLUMN status ENUM('Active', 'Lost') DEFAULT 'Active';

-- Create lost_pet_alerts table
CREATE TABLE IF NOT EXISTS lost_pet_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    user_id INT NOT NULL,
    last_seen_location VARCHAR(255) NOT NULL,
    last_seen_date DATE NOT NULL,
    description TEXT,
    status ENUM('Active', 'Resolved') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES user_pets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create found_pet_reports table
CREATE TABLE IF NOT EXISTS found_pet_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    user_id INT NOT NULL,
    found_location VARCHAR(255) NOT NULL,
    found_date DATE NOT NULL,
    notes TEXT,
    contact_info VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alert_id) REFERENCES lost_pet_alerts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
