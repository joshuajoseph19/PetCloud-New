-- First, check if users table has a 'role' column
-- If not, add it
ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) DEFAULT 'client' AFTER `profile_pic`;

-- Now insert or update admin user
-- Using a simple password hash for 'admin'
DELETE FROM `users` WHERE email = 'admin@gmail.com';

INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`, `created_at`) 
VALUES 
('System Administrator', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW());

-- Verify it was created
SELECT * FROM users WHERE email = 'admin@gmail.com';
