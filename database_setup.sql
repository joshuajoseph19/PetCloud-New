-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `petcloud_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `petcloud_db`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(500) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin user
-- Password is 'admin' (hashed)
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`, `created_at`) 
VALUES 
('System Administrator', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW())
ON DUPLICATE KEY UPDATE 
`password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
`role` = 'admin';

-- Verify admin was created
SELECT 'Admin user created successfully!' as message, email, role FROM users WHERE email = 'admin@gmail.com';
