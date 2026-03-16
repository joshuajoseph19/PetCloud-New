-- =====================================================
-- PetCloud - Pet Rehoming Database Schema
-- =====================================================
-- This schema supports dynamic, database-driven breed selection
-- with proper normalization and filtering capabilities
-- =====================================================

-- 1. Pet Types Table
-- Stores main pet categories (Dog, Cat, Bird, Rabbit, etc.)
CREATE TABLE IF NOT EXISTS pet_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(100) DEFAULT NULL, -- Optional: for UI icons
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Breed Groups Table
-- Stores breed categories (Pure Breed, Mixed Breed, Indie/Local)
CREATE TABLE IF NOT EXISTS breed_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Breeds Table
-- Stores all breed information with relationships
CREATE TABLE IF NOT EXISTS breeds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_type_id INT NOT NULL,
    breed_group_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    characteristics TEXT DEFAULT NULL, -- Optional: breed traits
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (pet_type_id) REFERENCES pet_types(id) ON DELETE CASCADE,
    FOREIGN KEY (breed_group_id) REFERENCES breed_groups(id) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX idx_pet_type (pet_type_id),
    INDEX idx_breed_group (breed_group_id),
    INDEX idx_active (is_active),
    INDEX idx_name (name),
    INDEX idx_composite (pet_type_id, breed_group_id, is_active),
    
    -- Ensure unique breed names per pet type
    UNIQUE KEY unique_breed_per_type (pet_type_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Pet Rehoming Listings Table
-- Main table for rehoming submissions
CREATE TABLE IF NOT EXISTS pet_rehoming_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- FK to users table
    pet_type_id INT NOT NULL,
    breed_id INT DEFAULT NULL, -- NULL if "Unknown/Not Sure"
    
    -- Pet Details
    pet_name VARCHAR(100) NOT NULL,
    age_years INT DEFAULT NULL,
    age_months INT DEFAULT NULL,
    gender ENUM('Male', 'Female', 'Unknown') NOT NULL,
    size ENUM('Small', 'Medium', 'Large', 'Extra Large') DEFAULT NULL,
    color VARCHAR(100) DEFAULT NULL,
    
    -- Health & Behavior
    is_vaccinated TINYINT(1) DEFAULT 0,
    is_neutered TINYINT(1) DEFAULT 0,
    health_status TEXT DEFAULT NULL,
    temperament TEXT DEFAULT NULL,
    special_needs TEXT DEFAULT NULL,
    
    -- Rehoming Details
    reason_for_rehoming TEXT NOT NULL,
    adoption_fee DECIMAL(10, 2) DEFAULT 0.00,
    location VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) DEFAULT NULL,
    
    -- Contact & Media
    contact_phone VARCHAR(20) DEFAULT NULL,
    contact_email VARCHAR(255) DEFAULT NULL,
    primary_image VARCHAR(255) DEFAULT NULL,
    additional_images TEXT DEFAULT NULL, -- JSON array of image paths
    
    -- Status & Metadata
    status ENUM('Pending', 'Approved', 'Adopted', 'Rejected', 'Withdrawn') DEFAULT 'Pending',
    views_count INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    adopted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (pet_type_id) REFERENCES pet_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (breed_id) REFERENCES breeds(id) ON DELETE SET NULL,
    
    -- Indexes for filtering and performance
    INDEX idx_user (user_id),
    INDEX idx_pet_type (pet_type_id),
    INDEX idx_breed (breed_id),
    INDEX idx_status (status),
    INDEX idx_location (city, state),
    INDEX idx_featured (is_featured),
    INDEX idx_created (created_at),
    INDEX idx_filter_composite (status, pet_type_id, breed_id, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Adoption Inquiries Table (Optional - for tracking interest)
CREATE TABLE IF NOT EXISTS adoption_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    inquirer_user_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('New', 'Contacted', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (listing_id) REFERENCES pet_rehoming_listings(id) ON DELETE CASCADE,
    INDEX idx_listing (listing_id),
    INDEX idx_inquirer (inquirer_user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA - Initial Setup
-- =====================================================

-- Insert Pet Types
INSERT INTO pet_types (name, icon, display_order, is_active) VALUES
('Dog', 'fa-dog', 1, 1),
('Cat', 'fa-cat', 2, 1),
('Bird', 'fa-dove', 3, 1),
('Rabbit', 'fa-rabbit', 4, 1),
('Fish', 'fa-fish', 5, 1),
('Hamster', 'fa-hamster', 6, 1),
('Other', 'fa-paw', 7, 1);

-- Insert Breed Groups
INSERT INTO breed_groups (name, description, display_order, is_active) VALUES
('Pure Breed', 'Purebred animals with documented lineage', 1, 1),
('Mixed Breed', 'Mixed or crossbreed animals', 2, 1),
('Indie / Local', 'Indigenous or local breeds', 3, 1),
('Unknown', 'Breed type not determined or not sure', 4, 1);

-- Insert Sample Breeds for Dogs
INSERT INTO breeds (pet_type_id, breed_group_id, name, description) VALUES
-- Pure Breed Dogs
(1, 1, 'Labrador Retriever', 'Friendly, active, and outgoing'),
(1, 1, 'German Shepherd', 'Intelligent, confident, and courageous'),
(1, 1, 'Golden Retriever', 'Friendly, intelligent, and devoted'),
(1, 1, 'Beagle', 'Friendly, curious, and merry'),
(1, 1, 'Pomeranian', 'Inquisitive, bold, and lively'),
(1, 1, 'Shih Tzu', 'Affectionate, playful, and outgoing'),
(1, 1, 'Pug', 'Charming, mischievous, and loving'),
(1, 1, 'Rottweiler', 'Loyal, loving, and confident guardian'),
(1, 1, 'Doberman Pinscher', 'Alert, fearless, and loyal'),
(1, 1, 'Cocker Spaniel', 'Gentle, smart, and happy'),

-- Mixed Breed Dogs
(1, 2, 'Labradoodle', 'Labrador + Poodle mix'),
(1, 2, 'Cockapoo', 'Cocker Spaniel + Poodle mix'),
(1, 2, 'Mixed Breed - Small', 'Small mixed breed dog'),
(1, 2, 'Mixed Breed - Medium', 'Medium mixed breed dog'),
(1, 2, 'Mixed Breed - Large', 'Large mixed breed dog'),

-- Indie / Local Dogs
(1, 3, 'Indian Pariah Dog', 'Indigenous Indian breed, highly adaptable'),
(1, 3, 'Rajapalayam', 'Indian sighthound from Tamil Nadu'),
(1, 3, 'Kombai', 'Indian hunting dog from Tamil Nadu'),
(1, 3, 'Chippiparai', 'Indian sighthound breed'),
(1, 3, 'Indian Spitz', 'Popular Indian companion dog'),

-- Unknown
(1, 4, 'Unknown / Not Sure', 'Breed not determined');

-- Insert Sample Breeds for Cats
INSERT INTO breeds (pet_type_id, breed_group_id, name, description) VALUES
-- Pure Breed Cats
(2, 1, 'Persian', 'Long-haired, gentle, and calm'),
(2, 1, 'Siamese', 'Vocal, social, and intelligent'),
(2, 1, 'Maine Coon', 'Large, friendly, and playful'),
(2, 1, 'British Shorthair', 'Easy-going, calm, and affectionate'),
(2, 1, 'Bengal', 'Active, playful, and energetic'),
(2, 1, 'Ragdoll', 'Docile, gentle, and affectionate'),
(2, 1, 'Sphynx', 'Energetic, loyal, and dog-like'),

-- Mixed Breed Cats
(2, 2, 'Mixed Breed - Short Hair', 'Short-haired mixed breed cat'),
(2, 2, 'Mixed Breed - Long Hair', 'Long-haired mixed breed cat'),

-- Indie / Local Cats
(2, 3, 'Indian Street Cat', 'Indigenous Indian cat, highly adaptable'),
(2, 3, 'Domestic Short Hair', 'Common domestic cat'),

-- Unknown
(2, 4, 'Unknown / Not Sure', 'Breed not determined');

-- Insert Sample Breeds for Birds
INSERT INTO breeds (pet_type_id, breed_group_id, name, description) VALUES
-- Pure Breed Birds
(3, 1, 'Budgerigar (Budgie)', 'Small, colorful, and social'),
(3, 1, 'Cockatiel', 'Friendly and easy to train'),
(3, 1, 'Lovebird', 'Affectionate and social'),
(3, 1, 'Parrot', 'Intelligent and talkative'),
(3, 1, 'Canary', 'Beautiful singers'),
(3, 1, 'Finch', 'Small and active'),

-- Unknown
(3, 4, 'Unknown / Not Sure', 'Breed not determined');

-- Insert Sample Breeds for Rabbits
INSERT INTO breeds (pet_type_id, breed_group_id, name, description) VALUES
-- Pure Breed Rabbits
(4, 1, 'Holland Lop', 'Small and friendly'),
(4, 1, 'Netherland Dwarf', 'Tiny and energetic'),
(4, 1, 'Flemish Giant', 'Large and gentle'),
(4, 1, 'Lionhead', 'Distinctive mane, friendly'),
(4, 1, 'Mini Rex', 'Soft fur, calm temperament'),

-- Mixed Breed
(4, 2, 'Mixed Breed', 'Mixed breed rabbit'),

-- Unknown
(4, 4, 'Unknown / Not Sure', 'Breed not determined');

-- For other pet types (Fish, Hamster, Other), add "Unknown" option
INSERT INTO breeds (pet_type_id, breed_group_id, name, description) VALUES
(5, 4, 'Unknown / Not Sure', 'Breed not determined'),
(6, 4, 'Unknown / Not Sure', 'Breed not determined'),
(7, 4, 'Unknown / Not Sure', 'Breed not determined');

-- =====================================================
-- USEFUL QUERIES FOR REFERENCE
-- =====================================================

-- Get all breeds for a specific pet type, grouped by breed group
-- Example: Get all dog breeds
/*
SELECT 
    bg.name AS breed_group,
    b.id AS breed_id,
    b.name AS breed_name,
    b.description
FROM breeds b
JOIN breed_groups bg ON b.breed_group_id = bg.id
WHERE b.pet_type_id = 1 -- Dog
  AND b.is_active = 1
  AND bg.is_active = 1
ORDER BY bg.display_order, b.name;
*/

-- Get all active pet types
/*
SELECT id, name, icon 
FROM pet_types 
WHERE is_active = 1 
ORDER BY display_order;
*/

-- Filter rehoming listings by pet type, breed group, and breed
/*
SELECT 
    prl.*,
    pt.name AS pet_type,
    b.name AS breed_name,
    bg.name AS breed_group
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
LEFT JOIN breed_groups bg ON b.breed_group_id = bg.id
WHERE prl.status = 'Approved'
  AND prl.pet_type_id = 1 -- Optional: filter by pet type
  AND (prl.breed_id = 5 OR prl.breed_id IS NULL) -- Optional: filter by breed
ORDER BY prl.created_at DESC;
*/
