-- =====================================================
-- PetCloud - Expanded Service Architecture Schema
-- =====================================================

-- 1. Service Categories Table
-- High-level grouping for UI (e.g., "Medical", "Grooming")
CREATE TABLE IF NOT EXISTS service_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(100) DEFAULT NULL, -- FontAwesome class or image URL
    description TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Master Services Table
-- The canonical list of all possible services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    
    -- Default attributes (can be overridden by clinics)
    default_duration_minutes INT DEFAULT 30,
    is_medical TINYINT(1) DEFAULT 0, -- 1=Medical record required, 0=Lifestyle/Grooming
    
    is_home_service_supported TINYINT(1) DEFAULT 0, -- Can this be done at home?
    is_clinic_service_supported TINYINT(1) DEFAULT 1, -- Can this be done at clinic?
    
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Service-Pet Type Compatibility
-- Defines which services apply to which pets (e.g., "Beak Trimming" for Birds only)
CREATE TABLE IF NOT EXISTS service_pet_type_compatibility (
    service_id INT NOT NULL,
    pet_type_id INT NOT NULL, -- References existing pet_types table
    PRIMARY KEY (service_id, pet_type_id),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_type_id) REFERENCES pet_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Clinic / Provider Service Settings
-- Connects a Service to a Clinic (Shop/Service Provider)
-- Allows overriding price, duration, and availability
CREATE TABLE IF NOT EXISTS clinic_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clinic_id INT NOT NULL, -- References users table (role=shop_owner/vet)
    service_id INT NOT NULL,
    
    -- Customization
    price DECIMAL(10, 2) NOT NULL,
    duration_minutes INT NOT NULL,
    deposit_required DECIMAL(10, 2) DEFAULT 0.00,
    
    -- Availability status
    is_available TINYINT(1) DEFAULT 1,
    is_home_visit_available TINYINT(1) DEFAULT 0,
    
    notes TEXT, -- Example: "Bring vaccination card"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (clinic_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_clinic_service (clinic_id, service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SEED DATA - Expanded Service List
-- =====================================================

-- 1. Insert Categories
INSERT INTO service_categories (name, slug, icon, display_order) VALUES
('Medical Consultation', 'medical', 'fa-user-md', 1),
('Preventive Care', 'preventive', 'fa-shield-virus', 2),
('Grooming & Spa', 'grooming', 'fa-pump-soap', 3),
('Diagnostics', 'diagnostics', 'fa-microscope', 4),
('Surgery & Dental', 'surgery', 'fa-syringe', 5),
('Alternative Therapy', 'therapy', 'fa-spa', 6),
('Training & Behavior', 'training', 'fa-graduation-cap', 7),
('Boarding & Daycare', 'boarding', 'fa-home', 8);

-- 2. Insert Services

-- Medical Consultation
INSERT INTO services (category_id, name, default_duration_minutes, is_medical) VALUES
(1, 'General Checkup', 20, 1),
(1, 'Emergency Consultation', 30, 1),
(1, 'Video Consultation', 15, 1),
(1, 'Specialist Consultation (Dermatology/Ortho)', 45, 1),
(1, 'Follow-up Visit', 15, 1);

-- Preventive Care
INSERT INTO services (category_id, name, default_duration_minutes, is_medical) VALUES
(2, 'Vaccination', 15, 1),
(2, 'Deworming', 10, 1),
(2, 'Microchipping', 20, 1),
(2, 'Tick & Flea Treatment', 30, 0),
(2, 'Diet & Nutrition Counseling', 30, 0);

-- Grooming & Spa
INSERT INTO services (category_id, name, default_duration_minutes, is_medical, is_home_service_supported) VALUES
(3, 'Basic Bath & Blow Dry', 45, 0, 1),
(3, 'Full Grooming Package', 90, 0, 1),
(3, 'Nail Clipping', 15, 0, 1),
(3, 'Ear Cleaning', 15, 0, 1),
(3, 'Sanitary Trim', 20, 0, 1),
(3, 'Medicated Bath', 60, 1, 0),
(3, 'De-shedding Treatment', 60, 0, 1);

-- Diagnostics
INSERT INTO services (category_id, name, default_duration_minutes, is_medical) VALUES
(4, 'Blood Test (CBC/Biochem)', 15, 1),
(4, 'X-Ray / Radiography', 30, 1),
(4, 'Ultrasound', 45, 1),
(4, 'Urine/Stool Analysis', 15, 1),
(4, 'Allergy Testing', 30, 1);

-- Surgery & Dental
INSERT INTO services (category_id, name, default_duration_minutes, is_medical) VALUES
(5, 'Spay / Neuter Surgery', 120, 1),
(5, 'Dental Scaling & Polishing', 60, 1),
(5, 'Tooth Extraction', 90, 1),
(5, 'Wound Dressing / Suturing', 30, 1),
(5, 'Minor Surgical Procedure', 60, 1);

-- Alternative Therapy
INSERT INTO services (category_id, name, default_duration_minutes, is_medical) VALUES
(6, 'Physiotherapy Session', 45, 1),
(6, 'Acupuncture', 45, 1),
(6, 'Laser Therapy', 30, 1),
(6, 'Hydrotherapy', 45, 0);

-- Training & Behavior
INSERT INTO services (category_id, name, default_duration_minutes, is_medical, is_home_service_supported) VALUES
(7, 'Obedience Training', 60, 0, 1),
(7, 'Puppy Socialization', 60, 0, 1),
(7, 'Behavioral Modification', 90, 1, 1),
(7, 'Agility Training', 60, 0, 0);

-- Boarding & Daycare
INSERT INTO services (category_id, name, default_duration_minutes, is_medical) VALUES
(8, 'Daycare (Full Day)', 480, 0),
(8, 'Overnight Boarding', 1440, 0),
(8, 'Pet Sitting (Hourly)', 60, 0);

