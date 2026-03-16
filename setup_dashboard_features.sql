CREATE TABLE IF NOT EXISTS feeding_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_name VARCHAR(100),
    meal_name VARCHAR(50), -- Breakfast, Lunch, Dinner
    food_description VARCHAR(255),
    feeding_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert dummy data for the user (to make it look "functional" immediately)
-- Assuming user_id 1 is the main user or whoever acts. 
-- But better to insert via PHP if user_id is dynamic. 
-- We will handle seeding in PHP if table is empty.
