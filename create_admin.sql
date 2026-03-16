-- Create admin user in the database
-- Run this SQL query in phpMyAdmin

INSERT INTO `users` (`full_name`, `email`, `password_hash`, `google_id`, `profile_pic`, `created_at`, `role`) 
VALUES 
('Admin User', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NOW(), 'admin')
ON DUPLICATE KEY UPDATE 
`email` = 'admin@gmail.com', 
`role` = 'admin';

-- Note: The password hash above is for the password "admin"
-- You can generate a new hash using PHP: password_hash('admin', PASSWORD_DEFAULT)

-- If you need to set a simple password for testing, use this:
-- UPDATE users SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@gmail.com';
