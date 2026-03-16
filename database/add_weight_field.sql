-- Add weight field to pet_rehoming_listings table
-- Weight is stored in kilograms (kg)

ALTER TABLE pet_rehoming_listings 
ADD COLUMN weight_kg DECIMAL(5, 2) DEFAULT NULL COMMENT 'Weight in kilograms' 
AFTER size;

-- Add index for potential filtering by weight
ALTER TABLE pet_rehoming_listings 
ADD INDEX idx_weight (weight_kg);
