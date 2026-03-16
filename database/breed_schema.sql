-- =====================================================
-- PetCloud - Breed System Schema
-- =====================================================

-- 1. Pet Types (Animal Types)
-- Normalized table for Dog, Cat, Bird, etc.
CREATE TABLE IF NOT EXISTS adoption_pet_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE, -- Dog, Cat
    slug VARCHAR(50) NOT NULL UNIQUE, -- dog, cat
    icon VARCHAR(50) DEFAULT 'fa-paw', -- FontAwesome class
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Breed Categories
-- Groups breeds (e.g., "Herding Dogs", "Parrots")
CREATE TABLE IF NOT EXISTS breed_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_type_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_type_id) REFERENCES adoption_pet_types(id) ON DELETE CASCADE,
    INDEX idx_pet_type (pet_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Breeds
-- Specific breeds linked to a category
CREATE TABLE IF NOT EXISTS adoption_breeds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES breed_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA
-- =====================================================

-- 1. Insert Pet Types
INSERT INTO adoption_pet_types (name, slug, icon, display_order) VALUES
('Dog', 'dog', 'fa-dog', 1),
('Cat', 'cat', 'fa-cat', 2),
('Bird', 'bird', 'fa-dove', 3),
('Rabbit', 'rabbit', 'fa-carrot', 4);

-- 2. Insert Breed Categories & Breeds

-- --- DOGS ---
SET @dog_id = (SELECT id FROM adoption_pet_types WHERE slug = 'dog');

INSERT INTO breed_categories (pet_type_id, name) VALUES 
(@dog_id, 'Sporting Group'),
(@dog_id, 'Herding Group'),
(@dog_id, 'Toy Group'),
(@dog_id, 'Mixed/Other');

-- Dog Breeds
SET @sporting_id = (SELECT id FROM breed_categories WHERE name = 'Sporting Group' AND pet_type_id = @dog_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@sporting_id, 'Golden Retriever'),
(@sporting_id, 'Labrador Retriever'),
(@sporting_id, 'Cocker Spaniel');

SET @herding_id = (SELECT id FROM breed_categories WHERE name = 'Herding Group' AND pet_type_id = @dog_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@herding_id, 'German Shepherd'),
(@herding_id, 'Border Collie'),
(@herding_id, 'Australian Shepherd');

SET @toy_id = (SELECT id FROM breed_categories WHERE name = 'Toy Group' AND pet_type_id = @dog_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@toy_id, 'Pug'),
(@toy_id, 'Pomeranian'),
(@toy_id, 'Chihuahua');

SET @dog_mixed_id = (SELECT id FROM breed_categories WHERE name = 'Mixed/Other' AND pet_type_id = @dog_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@dog_mixed_id, 'Indie (Desi Dog)'),
(@dog_mixed_id, 'Mixed Breed'),
(@dog_mixed_id, 'Unknown');

-- --- CATS ---
SET @cat_id = (SELECT id FROM adoption_pet_types WHERE slug = 'cat');

INSERT INTO breed_categories (pet_type_id, name) VALUES 
(@cat_id, 'Short Hair'),
(@cat_id, 'Long Hair'),
(@cat_id, 'Exotic');

-- Cat Breeds
SET @shorthair_id = (SELECT id FROM breed_categories WHERE name = 'Short Hair' AND pet_type_id = @cat_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@shorthair_id, 'Domestic Short Hair'),
(@shorthair_id, 'American Shorthair'),
(@shorthair_id, 'British Shorthair');

SET @longhair_id = (SELECT id FROM breed_categories WHERE name = 'Long Hair' AND pet_type_id = @cat_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@longhair_id, 'Persian'),
(@longhair_id, 'Maine Coon'),
(@longhair_id, 'Ragdoll');

SET @exotic_id = (SELECT id FROM breed_categories WHERE name = 'Exotic' AND pet_type_id = @cat_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@exotic_id, 'Siamese'),
(@exotic_id, 'Bengal'),
(@exotic_id, 'Sphynx');

-- --- BIRDS ---
SET @bird_id = (SELECT id FROM adoption_pet_types WHERE slug = 'bird');

INSERT INTO breed_categories (pet_type_id, name) VALUES 
(@bird_id, 'Parrots'),
(@bird_id, 'Finches & Canaries'),
(@bird_id, 'Other');

SET @parrot_id = (SELECT id FROM breed_categories WHERE name = 'Parrots' AND pet_type_id = @bird_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@parrot_id, 'African Grey'),
(@parrot_id, 'Macaw'),
(@parrot_id, 'Cockatiel'),
(@parrot_id, 'Budgie');

-- --- RABBITS ---
SET @rabbit_id = (SELECT id FROM adoption_pet_types WHERE slug = 'rabbit');

INSERT INTO breed_categories (pet_type_id, name) VALUES 
(@rabbit_id, 'Fancy Breeds'),
(@rabbit_id, 'Large Breeds');

SET @rabbit_fancy_id = (SELECT id FROM breed_categories WHERE name = 'Fancy Breeds' AND pet_type_id = @rabbit_id);
INSERT INTO adoption_breeds (category_id, name) VALUES 
(@rabbit_fancy_id, 'Holland Lop'),
(@rabbit_fancy_id, 'Netherland Dwarf'),
(@rabbit_fancy_id, 'Lionhead');
