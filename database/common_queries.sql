-- =====================================================
-- PetCloud - Common SQL Queries Reference
-- Quick reference for frequently used queries
-- =====================================================

-- =====================================================
-- 1. BREED MANAGEMENT QUERIES
-- =====================================================

-- Get all breeds for a specific pet type, grouped by breed group
SELECT 
    bg.name AS breed_group,
    bg.display_order AS group_order,
    b.id AS breed_id,
    b.name AS breed_name,
    b.description
FROM breeds b
JOIN breed_groups bg ON b.breed_group_id = bg.id
WHERE b.pet_type_id = 1 -- Change to desired pet type ID
  AND b.is_active = 1
  AND bg.is_active = 1
ORDER BY bg.display_order, b.name;

-- Count breeds per pet type
SELECT 
    pt.name AS pet_type,
    COUNT(b.id) AS breed_count
FROM pet_types pt
LEFT JOIN breeds b ON pt.id = b.pet_type_id AND b.is_active = 1
WHERE pt.is_active = 1
GROUP BY pt.id, pt.name
ORDER BY pt.display_order;

-- Find breeds by name (search)
SELECT 
    b.id,
    b.name AS breed_name,
    pt.name AS pet_type,
    bg.name AS breed_group
FROM breeds b
JOIN pet_types pt ON b.pet_type_id = pt.id
JOIN breed_groups bg ON b.breed_group_id = bg.id
WHERE b.name LIKE '%labrador%'
  AND b.is_active = 1;

-- Add a new breed
INSERT INTO breeds (pet_type_id, breed_group_id, name, description)
VALUES (1, 1, 'Australian Shepherd', 'Intelligent, energetic herding dog');

-- Update breed information
UPDATE breeds 
SET description = 'Updated description',
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1;

-- Deactivate a breed (soft delete)
UPDATE breeds 
SET is_active = 0,
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1;

-- =====================================================
-- 2. LISTING QUERIES
-- =====================================================

-- Get all approved listings with full details
SELECT 
    prl.id,
    prl.pet_name,
    prl.age_years,
    prl.age_months,
    prl.gender,
    prl.size,
    prl.color,
    prl.is_vaccinated,
    prl.is_neutered,
    prl.adoption_fee,
    prl.city,
    prl.state,
    prl.primary_image,
    prl.views_count,
    prl.created_at,
    pt.name AS pet_type,
    pt.icon AS pet_type_icon,
    b.name AS breed_name,
    bg.name AS breed_group,
    u.username AS owner_username,
    u.email AS owner_email
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
LEFT JOIN breed_groups bg ON b.breed_group_id = bg.id
LEFT JOIN users u ON prl.user_id = u.id
WHERE prl.status = 'Approved'
ORDER BY prl.is_featured DESC, prl.created_at DESC
LIMIT 20;

-- Filter listings by multiple criteria
SELECT 
    prl.id,
    prl.pet_name,
    prl.age_years,
    prl.age_months,
    prl.gender,
    prl.city,
    prl.state,
    pt.name AS pet_type,
    b.name AS breed_name,
    bg.name AS breed_group
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
LEFT JOIN breed_groups bg ON b.breed_group_id = bg.id
WHERE prl.status = 'Approved'
  AND prl.pet_type_id = 1 -- Dogs only
  AND bg.id = 1 -- Pure breeds only
  AND prl.city LIKE '%Mumbai%'
  AND prl.gender = 'Male'
  AND ((prl.age_years * 12) + COALESCE(prl.age_months, 0)) BETWEEN 12 AND 60 -- 1-5 years
ORDER BY prl.created_at DESC;

-- Get listings by user
SELECT 
    prl.id,
    prl.pet_name,
    prl.status,
    prl.views_count,
    prl.created_at,
    pt.name AS pet_type,
    b.name AS breed_name
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
WHERE prl.user_id = 123 -- Replace with actual user ID
ORDER BY prl.created_at DESC;

-- Get pending listings (for admin review)
SELECT 
    prl.id,
    prl.pet_name,
    prl.created_at,
    pt.name AS pet_type,
    u.username,
    u.email
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
JOIN users u ON prl.user_id = u.id
WHERE prl.status = 'Pending'
ORDER BY prl.created_at ASC;

-- Approve a listing
UPDATE pet_rehoming_listings 
SET status = 'Approved',
    approved_at = CURRENT_TIMESTAMP,
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1;

-- Mark listing as adopted
UPDATE pet_rehoming_listings 
SET status = 'Adopted',
    adopted_at = CURRENT_TIMESTAMP,
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1;

-- Increment view count
UPDATE pet_rehoming_listings 
SET views_count = views_count + 1
WHERE id = 1;

-- =====================================================
-- 3. STATISTICS & ANALYTICS
-- =====================================================

-- Count listings by status
SELECT 
    status,
    COUNT(*) AS count
FROM pet_rehoming_listings
GROUP BY status
ORDER BY count DESC;

-- Count listings by pet type
SELECT 
    pt.name AS pet_type,
    COUNT(prl.id) AS listing_count
FROM pet_types pt
LEFT JOIN pet_rehoming_listings prl ON pt.id = prl.pet_type_id
WHERE prl.status = 'Approved'
GROUP BY pt.id, pt.name
ORDER BY listing_count DESC;

-- Most popular breeds (by listing count)
SELECT 
    b.name AS breed_name,
    pt.name AS pet_type,
    COUNT(prl.id) AS listing_count
FROM breeds b
JOIN pet_types pt ON b.pet_type_id = pt.id
LEFT JOIN pet_rehoming_listings prl ON b.id = prl.breed_id
WHERE prl.status = 'Approved'
GROUP BY b.id, b.name, pt.name
ORDER BY listing_count DESC
LIMIT 10;

-- Listings by city (top 10)
SELECT 
    city,
    state,
    COUNT(*) AS listing_count
FROM pet_rehoming_listings
WHERE status = 'Approved'
GROUP BY city, state
ORDER BY listing_count DESC
LIMIT 10;

-- Average adoption fee by pet type
SELECT 
    pt.name AS pet_type,
    AVG(prl.adoption_fee) AS avg_fee,
    MIN(prl.adoption_fee) AS min_fee,
    MAX(prl.adoption_fee) AS max_fee,
    COUNT(*) AS listing_count
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
WHERE prl.status = 'Approved'
  AND prl.adoption_fee > 0
GROUP BY pt.id, pt.name
ORDER BY avg_fee DESC;

-- Adoption success rate (listings that got adopted)
SELECT 
    COUNT(CASE WHEN status = 'Adopted' THEN 1 END) AS adopted_count,
    COUNT(CASE WHEN status = 'Approved' THEN 1 END) AS active_count,
    COUNT(*) AS total_count,
    ROUND(COUNT(CASE WHEN status = 'Adopted' THEN 1 END) * 100.0 / COUNT(*), 2) AS adoption_rate
FROM pet_rehoming_listings
WHERE status IN ('Approved', 'Adopted');

-- Listings created per month (last 6 months)
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') AS month,
    COUNT(*) AS listing_count
FROM pet_rehoming_listings
WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month DESC;

-- Most viewed listings
SELECT 
    prl.id,
    prl.pet_name,
    prl.views_count,
    pt.name AS pet_type,
    b.name AS breed_name,
    prl.city
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
WHERE prl.status = 'Approved'
ORDER BY prl.views_count DESC
LIMIT 10;

-- =====================================================
-- 4. DATA VALIDATION & CLEANUP
-- =====================================================

-- Find listings with NULL breed_id
SELECT 
    id,
    pet_name,
    pet_type_id
FROM pet_rehoming_listings
WHERE breed_id IS NULL;

-- Find listings with invalid pet_type_id
SELECT 
    prl.id,
    prl.pet_name,
    prl.pet_type_id
FROM pet_rehoming_listings prl
LEFT JOIN pet_types pt ON prl.pet_type_id = pt.id
WHERE pt.id IS NULL;

-- Find orphaned breeds (no active listings)
SELECT 
    b.id,
    b.name AS breed_name,
    pt.name AS pet_type,
    COUNT(prl.id) AS listing_count
FROM breeds b
JOIN pet_types pt ON b.pet_type_id = pt.id
LEFT JOIN pet_rehoming_listings prl ON b.id = prl.breed_id AND prl.status = 'Approved'
WHERE b.is_active = 1
GROUP BY b.id, b.name, pt.name
HAVING listing_count = 0
ORDER BY pt.name, b.name;

-- Delete old rejected listings (older than 1 year)
DELETE FROM pet_rehoming_listings
WHERE status = 'Rejected'
  AND created_at < DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR);

-- Archive adopted listings (move to archive table - optional)
-- First create archive table if needed
CREATE TABLE IF NOT EXISTS pet_rehoming_listings_archive LIKE pet_rehoming_listings;

-- Then move old adopted listings
INSERT INTO pet_rehoming_listings_archive
SELECT * FROM pet_rehoming_listings
WHERE status = 'Adopted'
  AND adopted_at < DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR);

-- Delete from main table after archiving
DELETE FROM pet_rehoming_listings
WHERE status = 'Adopted'
  AND adopted_at < DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR);

-- =====================================================
-- 5. SEARCH & FILTERING EXAMPLES
-- =====================================================

-- Search by pet name
SELECT 
    prl.id,
    prl.pet_name,
    pt.name AS pet_type,
    b.name AS breed_name,
    prl.city
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
WHERE prl.status = 'Approved'
  AND prl.pet_name LIKE '%Max%'
ORDER BY prl.created_at DESC;

-- Find vaccinated dogs in a specific city
SELECT 
    prl.id,
    prl.pet_name,
    b.name AS breed_name,
    prl.age_years,
    prl.adoption_fee
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
WHERE prl.status = 'Approved'
  AND pt.name = 'Dog'
  AND prl.is_vaccinated = 1
  AND prl.city = 'Mumbai'
ORDER BY prl.created_at DESC;

-- Find free adoption pets (no fee)
SELECT 
    prl.id,
    prl.pet_name,
    pt.name AS pet_type,
    b.name AS breed_name,
    prl.city,
    prl.state
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
WHERE prl.status = 'Approved'
  AND prl.adoption_fee = 0
ORDER BY prl.created_at DESC;

-- Find young pets (under 1 year)
SELECT 
    prl.id,
    prl.pet_name,
    prl.age_years,
    prl.age_months,
    pt.name AS pet_type,
    b.name AS breed_name
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
WHERE prl.status = 'Approved'
  AND ((prl.age_years * 12) + COALESCE(prl.age_months, 0)) < 12
ORDER BY prl.created_at DESC;

-- =====================================================
-- 6. PERFORMANCE OPTIMIZATION
-- =====================================================

-- Check index usage
EXPLAIN SELECT * FROM pet_rehoming_listings 
WHERE status = 'Approved' 
  AND pet_type_id = 1 
  AND breed_id = 5;

-- Analyze table statistics
ANALYZE TABLE pet_rehoming_listings;
ANALYZE TABLE breeds;
ANALYZE TABLE pet_types;

-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
    table_rows
FROM information_schema.TABLES
WHERE table_schema = 'petcloud'
  AND table_name LIKE 'pet_%'
ORDER BY (data_length + index_length) DESC;

-- =====================================================
-- 7. BACKUP & RESTORE
-- =====================================================

-- Export specific tables (run from command line)
/*
mysqldump -u username -p petcloud pet_types breed_groups breeds pet_rehoming_listings > pet_rehoming_backup.sql
*/

-- Import backup (run from command line)
/*
mysql -u username -p petcloud < pet_rehoming_backup.sql
*/

-- =====================================================
-- 8. USEFUL VIEWS (Optional)
-- =====================================================

-- Create a view for active listings with all details
CREATE OR REPLACE VIEW v_active_pet_listings AS
SELECT 
    prl.id,
    prl.pet_name,
    prl.age_years,
    prl.age_months,
    ((prl.age_years * 12) + COALESCE(prl.age_months, 0)) AS total_age_months,
    prl.gender,
    prl.size,
    prl.color,
    prl.is_vaccinated,
    prl.is_neutered,
    prl.temperament,
    prl.adoption_fee,
    prl.location,
    prl.city,
    prl.state,
    prl.pincode,
    prl.primary_image,
    prl.views_count,
    prl.is_featured,
    prl.created_at,
    pt.id AS pet_type_id,
    pt.name AS pet_type,
    pt.icon AS pet_type_icon,
    b.id AS breed_id,
    b.name AS breed_name,
    bg.id AS breed_group_id,
    bg.name AS breed_group
FROM pet_rehoming_listings prl
JOIN pet_types pt ON prl.pet_type_id = pt.id
LEFT JOIN breeds b ON prl.breed_id = b.id
LEFT JOIN breed_groups bg ON b.breed_group_id = bg.id
WHERE prl.status = 'Approved';

-- Use the view
SELECT * FROM v_active_pet_listings
WHERE pet_type = 'Dog'
  AND city = 'Mumbai'
ORDER BY created_at DESC;

-- =====================================================
-- END OF REFERENCE QUERIES
-- =====================================================
