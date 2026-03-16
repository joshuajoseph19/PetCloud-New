-- 1. Create Adoption Listings Table
CREATE TABLE IF NOT EXISTS adoption_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,                      -- Linked to users table (for Normal Users)
    shop_id INT NULL,                      -- Linked to shop_applications (for Shop Owners)
    pet_name VARCHAR(100) NOT NULL,
    pet_type VARCHAR(50) NOT NULL,         -- dog, cat, bird, rabbit, other
    breed VARCHAR(100),
    age VARCHAR(50),
    gender ENUM('Male', 'Female', 'Unknown') DEFAULT 'Unknown',
    vaccination_status ENUM('Vaccinated', 'Not Vaccinated', 'Unknown') DEFAULT 'Unknown',
    description TEXT,
    reason_for_adoption TEXT,
    image_url TEXT,
    status ENUM('pending_approval', 'active', 'adopted', 'rejected') DEFAULT 'pending_approval',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    -- Note: shop_applications foreign key might differ based on existing schema
);

-- 2. Update Applications Table (Safe Alter)
-- We check if column exists first (or just run this, assuming fresh start for this feature)
-- If table exists, we modify it.
ALTER TABLE adoption_applications 
ADD COLUMN listing_id INT DEFAULT NULL AFTER user_id,
ADD CONSTRAINT fk_adoption_listing FOREIGN KEY (listing_id) REFERENCES adoption_listings(id) ON DELETE CASCADE;

-- 3. Seed some demo data (Optional)
-- INSERT INTO adoption_listings (pet_name, pet_type, breed, age, status, image_url) VALUES 
-- ('Buddy', 'dog', 'Golden Retriever', '2 years', 'active', 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=600');
