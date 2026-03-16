-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 10:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `petcloud_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoption_applications`
--

CREATE TABLE `adoption_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `pet_name` varchar(100) NOT NULL,
  `pet_category` varchar(50) DEFAULT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `applicant_email` varchar(255) NOT NULL,
  `applicant_phone` varchar(20) DEFAULT NULL,
  `reason_for_adoption` text DEFAULT NULL,
  `living_situation` varchar(100) DEFAULT NULL,
  `has_other_pets` tinyint(1) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_applications`
--

INSERT INTO `adoption_applications` (`id`, `user_id`, `listing_id`, `pet_name`, `pet_category`, `applicant_name`, `applicant_email`, `applicant_phone`, `reason_for_adoption`, `living_situation`, `has_other_pets`, `status`, `applied_at`) VALUES
(1, 2, 3, 'appu', 'dog', 'Joshua Joseph', 'joshuajoseph10310@gmail.com', '1234567890', 'fdsdfds', 'House', 1, 'pending', '2026-03-08 17:27:36');

-- --------------------------------------------------------

--
-- Table structure for table `adoption_breeds`
--

CREATE TABLE `adoption_breeds` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `adoption_breeds`
--

INSERT INTO `adoption_breeds` (`id`, `category_id`, `name`, `is_active`, `created_at`) VALUES
(1, 1, 'Golden Retriever', 1, '2026-03-08 13:14:17'),
(2, 1, 'Labrador Retriever', 1, '2026-03-08 13:14:17'),
(3, 5, 'Domestic Short Hair', 1, '2026-03-08 13:14:17'),
(4, 5, 'British Shorthair', 1, '2026-03-08 13:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `adoption_inquiries`
--

CREATE TABLE `adoption_inquiries` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `inquirer_user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('New','Contacted','In Progress','Completed','Cancelled') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adoption_listings`
--

CREATE TABLE `adoption_listings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `pet_name` varchar(100) NOT NULL,
  `pet_type` varchar(50) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female','Unknown') DEFAULT 'Unknown',
  `vaccination_status` enum('Vaccinated','Not Vaccinated','Unknown') DEFAULT 'Unknown',
  `description` text DEFAULT NULL,
  `reason_for_adoption` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `status` enum('pending_approval','active','adopted','rejected') DEFAULT 'pending_approval',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_listings`
--

INSERT INTO `adoption_listings` (`id`, `user_id`, `shop_id`, `pet_name`, `pet_type`, `breed`, `age`, `gender`, `vaccination_status`, `description`, `reason_for_adoption`, `image_url`, `status`, `created_at`) VALUES
(1, 2, NULL, 'jokku', 'dog', 'German Shepherd', '2 years', 'Male', 'Unknown', '', 'abroad', 'images/uploads/1772981087_german-shepherd-dog-2-years-old-lying_191971-5272.avif', 'active', '2026-03-08 14:44:47'),
(2, 4, NULL, 'akku', 'dog', 'Golden Retriever', '1', 'Female', 'Unknown', 'good', 'maintain issue', 'images/uploads/1772984895_login_dog.png', 'active', '2026-03-08 15:48:15'),
(3, 4, NULL, 'appu', 'dog', 'Pug', '3 years', 'Male', 'Unknown', 'only good habits', 'going abroad', 'images/uploads/1772987576_pug dog.jpg', 'active', '2026-03-08 16:32:56');

-- --------------------------------------------------------

--
-- Table structure for table `adoption_pet_types`
--

CREATE TABLE `adoption_pet_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-paw',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `adoption_pet_types`
--

INSERT INTO `adoption_pet_types` (`id`, `name`, `slug`, `icon`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Dog', 'dog', 'fa-dog', 1, 1, '2026-03-08 13:14:17'),
(2, 'Cat', 'cat', 'fa-cat', 2, 1, '2026-03-08 13:14:17'),
(3, 'Bird', 'bird', 'fa-dove', 3, 1, '2026-03-08 13:14:17'),
(4, 'Rabbit', 'rabbit', 'fa-carrot', 4, 1, '2026-03-08 13:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'Card',
  `hospital_id` int(11) DEFAULT NULL,
  `pet_name` varchar(100) DEFAULT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `service_type` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `provider_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `payment_id`, `payment_method`, `hospital_id`, `pet_name`, `breed`, `pet_id`, `service_type`, `title`, `cost`, `appointment_date`, `appointment_time`, `status`, `provider_name`, `description`, `created_at`) VALUES
(1, 2, NULL, 'Card', NULL, NULL, NULL, 3, 'Checkup', NULL, NULL, '2026-03-11', '10:30:00', 'pending', NULL, NULL, '2026-03-08 15:43:49'),
(2, 2, 'pay_SOnljUy77WbfQQ', 'Card', 1, 'enzo', 'Dog', NULL, 'Emergency Consultation', 'Emergency Consultation for enzo', 500.00, '2026-03-18', '09:00:00', 'confirmed', NULL, 'Scheduled Appointment', '2026-03-08 16:44:30'),
(3, 2, 'MOB_1773483310', 'Card', 1, 'Pet', 'Unknown', NULL, 'General', 'General for Pet', 0.00, '2026-03-15', '10:00:00', 'confirmed', NULL, 'Booked via Mobile App', '2026-03-14 10:15:10');

-- --------------------------------------------------------

--
-- Table structure for table `breeds`
--

CREATE TABLE `breeds` (
  `id` int(11) NOT NULL,
  `pet_type_id` int(11) NOT NULL,
  `breed_group_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `characteristics` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `breeds`
--

INSERT INTO `breeds` (`id`, `pet_type_id`, `breed_group_id`, `name`, `description`, `characteristics`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Labrador Retriever', 'Friendly, active, and outgoing', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(2, 1, 1, 'German Shepherd', 'Intelligent, confident, and courageous', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(3, 1, 1, 'Golden Retriever', 'Friendly, intelligent, and devoted', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(4, 1, 1, 'Beagle', 'Friendly, curious, and merry', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(5, 1, 1, 'Pomeranian', 'Inquisitive, bold, and lively', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(6, 1, 1, 'Shih Tzu', 'Affectionate, playful, and outgoing', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(7, 1, 1, 'Pug', 'Charming, mischievous, and loving', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(8, 1, 1, 'Rottweiler', 'Loyal, loving, and confident guardian', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(9, 1, 1, 'Doberman Pinscher', 'Alert, fearless, and loyal', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(10, 1, 1, 'Cocker Spaniel', 'Gentle, smart, and happy', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(11, 1, 2, 'Labradoodle', 'Labrador + Poodle mix', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(12, 1, 2, 'Cockapoo', 'Cocker Spaniel + Poodle mix', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(13, 1, 2, 'Mixed Breed - Small', 'Small mixed breed dog', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(14, 1, 2, 'Mixed Breed - Medium', 'Medium mixed breed dog', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(15, 1, 2, 'Mixed Breed - Large', 'Large mixed breed dog', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(16, 1, 3, 'Indian Pariah Dog', 'Indigenous Indian breed, highly adaptable', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(17, 1, 3, 'Rajapalayam', 'Indian sighthound from Tamil Nadu', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(18, 1, 3, 'Kombai', 'Indian hunting dog from Tamil Nadu', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(19, 1, 3, 'Chippiparai', 'Indian sighthound breed', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(20, 1, 3, 'Indian Spitz', 'Popular Indian companion dog', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(21, 1, 4, 'Unknown / Not Sure', 'Breed not determined', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(22, 2, 1, 'Persian', 'Long-haired, gentle, and calm', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(23, 2, 1, 'Siamese', 'Vocal, social, and intelligent', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(24, 2, 1, 'Maine Coon', 'Large, friendly, and playful', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(25, 2, 1, 'British Shorthair', 'Easy-going, calm, and affectionate', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(26, 2, 1, 'Bengal', 'Active, playful, and energetic', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(27, 2, 1, 'Ragdoll', 'Docile, gentle, and affectionate', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(28, 2, 1, 'Sphynx', 'Energetic, loyal, and dog-like', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(29, 2, 2, 'Mixed Breed - Short Hair', 'Short-haired mixed breed cat', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(30, 2, 2, 'Mixed Breed - Long Hair', 'Long-haired mixed breed cat', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(31, 2, 3, 'Indian Street Cat', 'Indigenous Indian cat, highly adaptable', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(32, 2, 3, 'Domestic Short Hair', 'Common domestic cat', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(33, 2, 4, 'Unknown / Not Sure', 'Breed not determined', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(34, 3, 1, 'Budgerigar (Budgie)', 'Small, colorful, and social', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(35, 3, 1, 'Cockatiel', 'Friendly and easy to train', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(36, 3, 1, 'Lovebird', 'Affectionate and social', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(37, 3, 1, 'Parrot', 'Intelligent and talkative', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(38, 3, 1, 'Canary', 'Beautiful singers', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(39, 3, 1, 'Finch', 'Small and active', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(40, 3, 4, 'Unknown / Not Sure', 'Breed not determined', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(41, 4, 1, 'Holland Lop', 'Small and friendly', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(42, 4, 1, 'Netherland Dwarf', 'Tiny and energetic', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(43, 4, 1, 'Flemish Giant', 'Large and gentle', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(44, 4, 1, 'Lionhead', 'Distinctive mane, friendly', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(45, 4, 1, 'Mini Rex', 'Soft fur, calm temperament', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(46, 4, 2, 'Mixed Breed', 'Mixed breed rabbit', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(47, 4, 4, 'Unknown / Not Sure', 'Breed not determined', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(48, 5, 4, 'Unknown / Not Sure', 'Breed not determined', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(49, 6, 4, 'Unknown / Not Sure', 'Breed not determined', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(50, 7, 4, 'Unknown / Not Sure', 'Breed not determined', NULL, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `breed_categories`
--

CREATE TABLE `breed_categories` (
  `id` int(11) NOT NULL,
  `pet_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `breed_categories`
--

INSERT INTO `breed_categories` (`id`, `pet_type_id`, `name`, `description`, `display_order`, `created_at`) VALUES
(1, 1, 'Sporting Group', NULL, 0, '2026-03-08 13:14:17'),
(2, 1, 'Herding Group', NULL, 0, '2026-03-08 13:14:17'),
(3, 1, 'Toy Group', NULL, 0, '2026-03-08 13:14:17'),
(4, 1, 'Mixed/Other', NULL, 0, '2026-03-08 13:14:17'),
(5, 2, 'Short Hair', NULL, 0, '2026-03-08 13:14:17'),
(6, 2, 'Long Hair', NULL, 0, '2026-03-08 13:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `breed_groups`
--

CREATE TABLE `breed_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `breed_groups`
--

INSERT INTO `breed_groups` (`id`, `name`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Pure Breed', 'Purebred animals with documented lineage', 1, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(2, 'Mixed Breed', 'Mixed or crossbreed animals', 2, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(3, 'Indie / Local', 'Indigenous or local breeds', 3, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(4, 'Unknown', 'Breed type not determined or not sure', 4, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_tasks`
--

CREATE TABLE `daily_tasks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `task_name` varchar(255) NOT NULL,
  `task_time` varchar(100) DEFAULT NULL,
  `task_date` date DEFAULT NULL,
  `frequency` enum('Once','Daily','Weekly','Monthly') DEFAULT 'Once',
  `is_done` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_tasks`
--

INSERT INTO `daily_tasks` (`id`, `user_id`, `pet_id`, `task_name`, `task_time`, `task_date`, `frequency`, `is_done`, `created_at`) VALUES
(1, 2, NULL, 'Check fixed health system', '20:00', '2026-03-08', 'Daily', 0, '2026-03-08 14:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `feeding_history`
--

CREATE TABLE `feeding_history` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `pet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fed_at` datetime DEFAULT current_timestamp(),
  `status` enum('completed','missed','skipped') DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feeding_history`
--

INSERT INTO `feeding_history` (`id`, `schedule_id`, `pet_id`, `user_id`, `fed_at`, `status`, `notes`, `created_at`) VALUES
(1, 1, 3, 2, '2026-03-08 20:17:41', 'completed', NULL, '2026-03-08 14:47:41'),
(2, 2, 3, 2, '2026-03-08 20:17:42', 'completed', NULL, '2026-03-08 14:47:42');

-- --------------------------------------------------------

--
-- Table structure for table `feeding_logs`
--

CREATE TABLE `feeding_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `feeding_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity_grams` int(11) NOT NULL,
  `status` enum('Success','Failed') DEFAULT 'Success',
  `message` varchar(255) DEFAULT 'Feeding completed successfully'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feeding_logs`
--

INSERT INTO `feeding_logs` (`id`, `user_id`, `pet_id`, `feeding_time`, `quantity_grams`, `status`, `message`) VALUES
(1, 2, 3, '2026-03-08 17:26:25', 30, 'Success', 'Manual feeding triggered from dashboard'),
(2, 2, 3, '2026-03-08 17:26:33', 30, 'Success', 'Manual feeding triggered from dashboard'),
(3, 2, 3, '2026-03-08 17:28:28', 30, 'Success', 'Manual feeding triggered from dashboard'),
(4, 2, 3, '2026-03-08 17:28:30', 30, 'Success', 'Manual feeding triggered from dashboard'),
(5, 2, 3, '2026-03-08 17:28:32', 30, 'Success', 'Manual feeding triggered from dashboard'),
(6, 2, 3, '2026-03-08 17:28:36', 60, 'Success', 'Manual feeding triggered from dashboard'),
(7, 2, 3, '2026-03-08 17:28:37', 30, 'Success', 'Manual feeding triggered from dashboard'),
(8, 2, 3, '2026-03-08 17:29:51', 100, 'Success', 'Manual feeding triggered from dashboard'),
(9, 2, 3, '2026-03-08 17:30:01', 30, 'Success', 'Manual feeding triggered from dashboard'),
(10, 2, 3, '2026-03-08 17:30:47', 30, 'Success', 'Manual feeding triggered from dashboard'),
(11, 2, 3, '2026-03-08 17:30:57', 100, 'Success', 'Manual feeding triggered from dashboard'),
(12, 2, 3, '2026-03-08 17:31:06', 60, 'Success', 'Manual feeding triggered from dashboard'),
(13, 2, 3, '2026-03-08 17:31:07', 30, 'Success', 'Manual feeding triggered from dashboard'),
(14, 2, 3, '2026-03-08 17:31:19', 100, 'Success', 'Manual feeding triggered from dashboard'),
(15, 2, 3, '2026-03-08 17:31:56', 30, 'Success', 'Manual feeding triggered from dashboard'),
(16, 2, 3, '2026-03-08 17:37:09', 30, 'Success', 'Manual feeding triggered from dashboard'),
(17, 2, 3, '2026-03-08 17:37:25', 30, 'Success', 'Manual feeding triggered from dashboard'),
(18, 2, 3, '2026-03-08 17:37:29', 60, 'Success', 'Manual feeding triggered from dashboard'),
(19, 2, 3, '2026-03-08 17:37:37', 30, 'Success', 'Manual feeding triggered from dashboard'),
(20, 2, 3, '2026-03-08 17:37:48', 100, 'Success', 'Manual feeding triggered from dashboard'),
(21, 2, 3, '2026-03-08 17:37:48', 30, 'Success', 'Manual feeding triggered from dashboard'),
(22, 2, 3, '2026-03-10 03:44:15', 30, 'Success', 'Manual feeding triggered from dashboard'),
(23, 2, 3, '2026-03-10 03:44:21', 30, 'Success', 'Manual feeding triggered from dashboard'),
(24, 2, 3, '2026-03-10 03:44:23', 30, 'Success', 'Manual feeding triggered from dashboard'),
(25, 2, 3, '2026-03-10 03:44:24', 60, 'Success', 'Manual feeding triggered from dashboard'),
(26, 2, 3, '2026-03-10 03:46:30', 100, 'Success', 'Manual feeding triggered from dashboard'),
(27, 2, 3, '2026-03-10 03:47:01', 60, 'Success', 'Manual feeding triggered from dashboard'),
(28, 2, 3, '2026-03-10 03:47:10', 30, 'Success', 'Manual feeding triggered from dashboard'),
(29, 2, 3, '2026-03-10 03:48:14', 100, 'Success', 'Manual feeding triggered from dashboard'),
(30, 2, 3, '2026-03-10 03:48:36', 30, 'Success', 'Manual feeding triggered from dashboard'),
(31, 2, 3, '2026-03-10 03:49:19', 100, 'Success', 'Manual feeding triggered from dashboard'),
(32, 2, 3, '2026-03-10 05:33:05', 60, 'Success', 'Manual feeding triggered from dashboard'),
(33, 2, 3, '2026-03-10 05:33:11', 100, 'Success', 'Manual feeding triggered from dashboard'),
(34, 2, 3, '2026-03-10 05:33:19', 60, 'Success', 'Manual feeding triggered from dashboard'),
(35, 2, 3, '2026-03-10 05:33:24', 100, 'Success', 'Manual feeding triggered from dashboard'),
(36, 2, 3, '2026-03-10 05:33:27', 100, 'Success', 'Manual feeding triggered from dashboard'),
(37, 2, 3, '2026-03-10 05:33:32', 100, 'Success', 'Manual feeding triggered from dashboard'),
(38, 2, 3, '2026-03-10 05:35:00', 30, 'Success', 'Manual feeding triggered from dashboard'),
(39, 2, 3, '2026-03-10 05:35:10', 30, 'Success', 'Manual feeding triggered from dashboard'),
(40, 2, 3, '2026-03-10 05:35:20', 60, 'Success', 'Manual feeding triggered from dashboard'),
(41, 2, 3, '2026-03-10 05:35:26', 100, 'Success', 'Manual feeding triggered from dashboard'),
(42, 2, 3, '2026-03-10 05:35:49', 60, 'Success', 'Manual feeding triggered from dashboard'),
(43, 2, 3, '2026-03-10 05:35:58', 30, 'Success', 'Manual feeding triggered from dashboard'),
(44, 2, 3, '2026-03-10 05:36:01', 30, 'Success', 'Manual feeding triggered from dashboard'),
(45, 2, 3, '2026-03-10 05:36:11', 30, 'Success', 'Manual feeding triggered from dashboard'),
(46, 2, 3, '2026-03-10 05:36:26', 60, 'Success', 'Manual feeding triggered from dashboard'),
(47, 2, 3, '2026-03-10 05:36:33', 60, 'Success', 'Manual feeding triggered from dashboard'),
(48, 2, 3, '2026-03-10 05:36:40', 100, 'Success', 'Manual feeding triggered from dashboard'),
(49, 2, 3, '2026-03-10 05:37:41', 60, 'Success', 'Manual feeding triggered from dashboard'),
(50, 2, 3, '2026-03-10 05:37:54', 100, 'Success', 'Manual feeding triggered from dashboard'),
(51, 2, 3, '2026-03-10 05:38:15', 60, 'Success', 'Manual feeding triggered from dashboard'),
(52, 2, 3, '2026-03-10 05:38:21', 100, 'Success', 'Manual feeding triggered from dashboard'),
(53, 2, 3, '2026-03-12 13:23:22', 30, 'Success', 'Manual feeding triggered from dashboard'),
(54, 2, 3, '2026-03-12 13:23:26', 30, 'Success', 'Manual feeding triggered from dashboard'),
(55, 2, 3, '2026-03-12 13:23:27', 30, 'Success', 'Manual feeding triggered from dashboard'),
(56, 2, 3, '2026-03-12 13:23:29', 30, 'Success', 'Manual feeding triggered from dashboard'),
(57, 2, 3, '2026-03-12 13:23:29', 30, 'Success', 'Manual feeding triggered from dashboard'),
(58, 2, 3, '2026-03-12 13:23:30', 30, 'Success', 'Manual feeding triggered from dashboard'),
(59, 2, 3, '2026-03-12 13:31:28', 100, 'Success', 'Manual feeding triggered from dashboard'),
(60, 2, 3, '2026-03-12 13:49:42', 60, 'Success', 'Manual feeding triggered from dashboard'),
(61, 2, 3, '2026-03-12 13:49:52', 100, 'Success', 'Manual feeding triggered from dashboard'),
(62, 2, 3, '2026-03-14 05:08:00', 30, 'Success', 'Manual feeding triggered from dashboard'),
(63, 2, 3, '2026-03-14 05:08:09', 100, 'Success', 'Manual feeding triggered from dashboard'),
(65, 2, 3, '2026-03-14 05:56:04', 100, 'Success', 'Manual feeding triggered from dashboard'),
(66, 2, 3, '2026-03-14 05:56:09', 60, 'Success', 'Manual feeding triggered from dashboard'),
(67, 2, 3, '2026-03-14 05:56:36', 100, 'Success', 'Manual feeding triggered from dashboard'),
(68, 2, 3, '2026-03-14 05:56:44', 60, 'Success', 'Manual feeding triggered from mobile app'),
(69, 2, 3, '2026-03-14 05:56:53', 30, 'Success', 'Manual feeding triggered from mobile app'),
(70, 2, 3, '2026-03-14 05:57:00', 100, 'Success', 'Manual feeding triggered from mobile app'),
(71, 2, 3, '2026-03-14 05:57:15', 60, 'Success', 'Manual feeding triggered from mobile app'),
(72, 2, 3, '2026-03-14 05:57:35', 100, 'Success', 'Manual feeding triggered from mobile app'),
(73, 2, 3, '2026-03-14 05:57:58', 30, 'Success', 'Manual feeding triggered from mobile app'),
(74, 2, 3, '2026-03-14 05:57:58', 30, 'Success', 'Manual feeding triggered from mobile app'),
(75, 2, 3, '2026-03-14 05:58:02', 30, 'Success', 'Manual feeding triggered from mobile app'),
(76, 2, 3, '2026-03-14 05:58:06', 100, 'Success', 'Manual feeding triggered from mobile app'),
(77, 2, 3, '2026-03-14 05:58:20', 100, 'Success', 'Manual feeding triggered from dashboard');

-- --------------------------------------------------------

--
-- Table structure for table `feeding_schedules`
--

CREATE TABLE `feeding_schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `meal_name` varchar(100) NOT NULL,
  `food_description` varchar(255) DEFAULT NULL,
  `feeding_time` time NOT NULL,
  `days_of_week` varchar(255) DEFAULT '["Mon","Tue","Wed","Thu","Fri","Sat","Sun"]',
  `portion_size` decimal(5,2) DEFAULT 0.00,
  `portion_unit` varchar(20) DEFAULT 'grams',
  `diet_type` varchar(50) DEFAULT 'Dry Food',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feeding_schedules`
--

INSERT INTO `feeding_schedules` (`id`, `user_id`, `pet_id`, `meal_name`, `food_description`, `feeding_time`, `days_of_week`, `portion_size`, `portion_unit`, `diet_type`, `is_active`, `created_at`) VALUES
(1, 2, 3, 'morning food', 'milk and egg', '07:30:00', '[\"Mon\",\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\",\"Sun\"]', 10.00, 'cups', 'Wet Food', 1, '2026-03-08 14:46:49'),
(2, 2, 3, 'evening food', 'chicken and rice ', '20:00:00', '[\"Mon\",\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\",\"Sun\"]', 50.00, 'items', 'Homemade', 1, '2026-03-08 14:47:38'),
(3, 2, 3, '', NULL, '08:00:00', '[\"Mon\", \"Tue\", \"Wed\", \"Thu\", \"Fri\", \"Sat\", \"Sun\"]', 0.00, 'grams', 'Dry Food', 1, '2026-03-08 15:43:49');

-- --------------------------------------------------------

--
-- Table structure for table `feed_commands`
--

CREATE TABLE `feed_commands` (
  `id` int(11) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `portion_qty` int(11) NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feed_commands`
--

INSERT INTO `feed_commands` (`id`, `device_id`, `portion_qty`, `status`, `created_at`) VALUES
(1, 'esp32_1', 30, '', '2026-03-08 17:28:28'),
(2, 'esp32_1', 30, '', '2026-03-08 17:28:30'),
(3, 'esp32_1', 30, '', '2026-03-08 17:28:32'),
(4, 'esp32_1', 60, '', '2026-03-08 17:28:36'),
(5, 'esp32_1', 30, '', '2026-03-08 17:28:37'),
(6, 'esp32_1', 100, '', '2026-03-08 17:29:51'),
(7, 'esp32_1', 30, '', '2026-03-08 17:30:01'),
(8, 'esp32_1', 30, '', '2026-03-08 17:30:47'),
(9, 'esp32_1', 100, '', '2026-03-08 17:30:57'),
(10, 'esp32_1', 60, '', '2026-03-08 17:31:06'),
(11, 'esp32_1', 30, '', '2026-03-08 17:31:07'),
(12, 'esp32_1', 100, '', '2026-03-08 17:31:19'),
(13, 'esp32_1', 30, '', '2026-03-08 17:31:56'),
(14, 'esp32_1', 30, '', '2026-03-08 17:37:09'),
(15, 'esp32_1', 30, '', '2026-03-08 17:37:25'),
(16, 'esp32_1', 60, '', '2026-03-08 17:37:29'),
(17, 'esp32_1', 30, '', '2026-03-08 17:37:37'),
(18, 'esp32_1', 100, '', '2026-03-08 17:37:48'),
(19, 'esp32_1', 30, '', '2026-03-08 17:37:48'),
(20, 'esp32_1', 30, '', '2026-03-10 03:44:15'),
(21, 'esp32_1', 30, '', '2026-03-10 03:44:21'),
(22, 'esp32_1', 30, '', '2026-03-10 03:44:23'),
(23, 'esp32_1', 60, '', '2026-03-10 03:44:24'),
(24, 'esp32_1', 100, '', '2026-03-10 03:46:30'),
(25, 'esp32_1', 60, '', '2026-03-10 03:47:01'),
(26, 'esp32_1', 30, '', '2026-03-10 03:47:10'),
(27, 'esp32_1', 100, '', '2026-03-10 03:48:14'),
(28, 'esp32_1', 30, '', '2026-03-10 03:48:36'),
(29, 'esp32_1', 100, '', '2026-03-10 03:49:19'),
(30, 'esp32_1', 60, '', '2026-03-10 05:33:05'),
(31, 'esp32_1', 100, '', '2026-03-10 05:33:11'),
(32, 'esp32_1', 60, '', '2026-03-10 05:33:19'),
(33, 'esp32_1', 100, '', '2026-03-10 05:33:24'),
(34, 'esp32_1', 100, '', '2026-03-10 05:33:27'),
(35, 'esp32_1', 100, '', '2026-03-10 05:33:32'),
(36, 'esp32_1', 30, '', '2026-03-10 05:35:00'),
(37, 'esp32_1', 30, '', '2026-03-10 05:35:10'),
(38, 'esp32_1', 60, '', '2026-03-10 05:35:20'),
(39, 'esp32_1', 100, '', '2026-03-10 05:35:26'),
(40, 'esp32_1', 60, '', '2026-03-10 05:35:49'),
(41, 'esp32_1', 30, '', '2026-03-10 05:35:58'),
(42, 'esp32_1', 30, '', '2026-03-10 05:36:01'),
(43, 'esp32_1', 30, '', '2026-03-10 05:36:11'),
(44, 'esp32_1', 60, '', '2026-03-10 05:36:26'),
(45, 'esp32_1', 60, '', '2026-03-10 05:36:33'),
(46, 'esp32_1', 100, '', '2026-03-10 05:36:40'),
(47, 'esp32_1', 60, '', '2026-03-10 05:37:41'),
(48, 'esp32_1', 100, '', '2026-03-10 05:37:54'),
(49, 'esp32_1', 60, '', '2026-03-10 05:38:15'),
(50, 'esp32_1', 100, '', '2026-03-10 05:38:21'),
(51, 'esp32_1', 30, '', '2026-03-12 13:23:22'),
(52, 'esp32_1', 30, '', '2026-03-12 13:23:26'),
(53, 'esp32_1', 30, '', '2026-03-12 13:23:27'),
(54, 'esp32_1', 30, '', '2026-03-12 13:23:29'),
(55, 'esp32_1', 30, '', '2026-03-12 13:23:29'),
(56, 'esp32_1', 30, '', '2026-03-12 13:23:30'),
(57, 'esp32_1', 100, '', '2026-03-12 13:31:28'),
(58, 'esp32_1', 60, '', '2026-03-12 13:49:42'),
(59, 'esp32_1', 100, '', '2026-03-12 13:49:52'),
(60, 'esp32_1', 30, '', '2026-03-14 05:08:00'),
(61, 'esp32_1', 100, '', '2026-03-14 05:08:09'),
(62, 'esp32_1', 30, '', '2026-03-14 05:49:32'),
(63, 'esp32_1', 100, '', '2026-03-14 05:56:04'),
(64, 'esp32_1', 60, '', '2026-03-14 05:56:09'),
(65, 'esp32_1', 100, '', '2026-03-14 05:56:36'),
(66, 'esp32_1', 60, '', '2026-03-14 05:56:44'),
(67, 'esp32_1', 30, '', '2026-03-14 05:56:53'),
(68, 'esp32_1', 100, '', '2026-03-14 05:57:00'),
(69, 'esp32_1', 60, '', '2026-03-14 05:57:15'),
(70, 'esp32_1', 100, '', '2026-03-14 05:57:35'),
(71, 'esp32_1', 30, '', '2026-03-14 05:57:58'),
(72, 'esp32_1', 30, '', '2026-03-14 05:57:59'),
(73, 'esp32_1', 30, '', '2026-03-14 05:58:02'),
(74, 'esp32_1', 100, '', '2026-03-14 05:58:06'),
(75, 'esp32_1', 100, '', '2026-03-14 05:58:20');

-- --------------------------------------------------------

--
-- Table structure for table `feed_logs`
--

CREATE TABLE `feed_logs` (
  `id` int(11) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `portion` int(11) NOT NULL,
  `fed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feed_logs`
--

INSERT INTO `feed_logs` (`id`, `device_id`, `portion`, `fed_at`) VALUES
(1, 'esp32_1', 30, '2026-03-08 17:28:28'),
(2, 'esp32_1', 30, '2026-03-08 17:28:30'),
(3, 'esp32_1', 30, '2026-03-08 17:28:32'),
(4, 'esp32_1', 60, '2026-03-08 17:28:36'),
(5, 'esp32_1', 30, '2026-03-08 17:28:37'),
(6, 'esp32_1', 30, '2026-03-08 17:28:54'),
(7, 'esp32_1', 30, '2026-03-08 17:29:01'),
(8, 'esp32_1', 100, '2026-03-08 17:29:51'),
(9, 'esp32_1', 30, '2026-03-08 17:29:58'),
(10, 'esp32_1', 30, '2026-03-08 17:30:01'),
(11, 'esp32_1', 60, '2026-03-08 17:30:06'),
(12, 'esp32_1', 30, '2026-03-08 17:30:11'),
(13, 'esp32_1', 100, '2026-03-08 17:30:25'),
(14, 'esp32_1', 30, '2026-03-08 17:30:30'),
(15, 'esp32_1', 30, '2026-03-08 17:30:47'),
(16, 'esp32_1', 30, '2026-03-08 17:30:49'),
(17, 'esp32_1', 100, '2026-03-08 17:30:57'),
(18, 'esp32_1', 100, '2026-03-08 17:31:02'),
(19, 'esp32_1', 60, '2026-03-08 17:31:06'),
(20, 'esp32_1', 30, '2026-03-08 17:31:07'),
(21, 'esp32_1', 60, '2026-03-08 17:31:11'),
(22, 'esp32_1', 30, '2026-03-08 17:31:15'),
(23, 'esp32_1', 100, '2026-03-08 17:31:19'),
(24, 'esp32_1', 100, '2026-03-08 17:31:25'),
(25, 'esp32_1', 30, '2026-03-08 17:31:56'),
(26, 'esp32_1', 30, '2026-03-08 17:32:02'),
(27, 'esp32_1', 30, '2026-03-08 17:37:09'),
(28, 'esp32_1', 30, '2026-03-08 17:37:12'),
(29, 'esp32_1', 30, '2026-03-08 17:37:25'),
(30, 'esp32_1', 30, '2026-03-08 17:37:29'),
(31, 'esp32_1', 60, '2026-03-08 17:37:29'),
(32, 'esp32_1', 60, '2026-03-08 17:37:34'),
(33, 'esp32_1', 30, '2026-03-08 17:37:37'),
(34, 'esp32_1', 30, '2026-03-08 17:37:42'),
(35, 'esp32_1', 100, '2026-03-08 17:37:48'),
(36, 'esp32_1', 30, '2026-03-08 17:37:48'),
(37, 'esp32_1', 100, '2026-03-08 17:37:52'),
(38, 'esp32_1', 30, '2026-03-08 17:37:56'),
(39, 'esp32_1', 30, '2026-03-10 03:44:15'),
(40, 'esp32_1', 30, '2026-03-10 03:44:21'),
(41, 'esp32_1', 30, '2026-03-10 03:44:23'),
(42, 'esp32_1', 60, '2026-03-10 03:44:24'),
(43, 'esp32_1', 30, '2026-03-10 03:45:38'),
(44, 'esp32_1', 30, '2026-03-10 03:45:52'),
(45, 'esp32_1', 30, '2026-03-10 03:46:06'),
(46, 'esp32_1', 60, '2026-03-10 03:46:16'),
(47, 'esp32_1', 100, '2026-03-10 03:46:30'),
(48, 'esp32_1', 100, '2026-03-10 03:46:35'),
(49, 'esp32_1', 60, '2026-03-10 03:47:01'),
(50, 'esp32_1', 60, '2026-03-10 03:47:09'),
(51, 'esp32_1', 30, '2026-03-10 03:47:10'),
(52, 'esp32_1', 30, '2026-03-10 03:47:14'),
(53, 'esp32_1', 100, '2026-03-10 03:48:14'),
(54, 'esp32_1', 100, '2026-03-10 03:48:25'),
(55, 'esp32_1', 30, '2026-03-10 03:48:36'),
(56, 'esp32_1', 30, '2026-03-10 03:48:39'),
(57, 'esp32_1', 100, '2026-03-10 03:49:19'),
(58, 'esp32_1', 100, '2026-03-10 03:49:25'),
(59, 'esp32_1', 60, '2026-03-10 05:33:05'),
(60, 'esp32_1', 60, '2026-03-10 05:33:10'),
(61, 'esp32_1', 100, '2026-03-10 05:33:11'),
(62, 'esp32_1', 100, '2026-03-10 05:33:16'),
(63, 'esp32_1', 60, '2026-03-10 05:33:19'),
(64, 'esp32_1', 60, '2026-03-10 05:33:23'),
(65, 'esp32_1', 100, '2026-03-10 05:33:24'),
(66, 'esp32_1', 100, '2026-03-10 05:33:27'),
(67, 'esp32_1', 100, '2026-03-10 05:33:31'),
(68, 'esp32_1', 100, '2026-03-10 05:33:32'),
(69, 'esp32_1', 100, '2026-03-10 05:33:38'),
(70, 'esp32_1', 100, '2026-03-10 05:33:46'),
(71, 'esp32_1', 30, '2026-03-10 05:35:00'),
(72, 'esp32_1', 30, '2026-03-10 05:35:03'),
(73, 'esp32_1', 30, '2026-03-10 05:35:10'),
(74, 'esp32_1', 30, '2026-03-10 05:35:18'),
(75, 'esp32_1', 60, '2026-03-10 05:35:20'),
(76, 'esp32_1', 60, '2026-03-10 05:35:25'),
(77, 'esp32_1', 100, '2026-03-10 05:35:26'),
(78, 'esp32_1', 100, '2026-03-10 05:35:33'),
(79, 'esp32_1', 60, '2026-03-10 05:35:49'),
(80, 'esp32_1', 60, '2026-03-10 05:35:53'),
(81, 'esp32_1', 30, '2026-03-10 05:35:58'),
(82, 'esp32_1', 30, '2026-03-10 05:36:01'),
(83, 'esp32_1', 30, '2026-03-10 05:36:09'),
(84, 'esp32_1', 30, '2026-03-10 05:36:11'),
(85, 'esp32_1', 30, '2026-03-10 05:36:14'),
(86, 'esp32_1', 30, '2026-03-10 05:36:19'),
(87, 'esp32_1', 60, '2026-03-10 05:36:26'),
(88, 'esp32_1', 60, '2026-03-10 05:36:32'),
(89, 'esp32_1', 60, '2026-03-10 05:36:33'),
(90, 'esp32_1', 100, '2026-03-10 05:36:40'),
(91, 'esp32_1', 60, '2026-03-10 05:36:41'),
(92, 'esp32_1', 100, '2026-03-10 05:36:49'),
(93, 'esp32_1', 60, '2026-03-10 05:37:41'),
(94, 'esp32_1', 60, '2026-03-10 05:37:51'),
(95, 'esp32_1', 100, '2026-03-10 05:37:54'),
(96, 'esp32_1', 100, '2026-03-10 05:37:59'),
(97, 'esp32_1', 60, '2026-03-10 05:38:15'),
(98, 'esp32_1', 100, '2026-03-10 05:38:21'),
(99, 'esp32_1', 60, '2026-03-10 05:38:29'),
(100, 'esp32_1', 100, '2026-03-10 05:38:36'),
(101, 'esp32_1', 30, '2026-03-12 13:23:22'),
(102, 'esp32_1', 30, '2026-03-12 13:23:26'),
(103, 'esp32_1', 30, '2026-03-12 13:23:27'),
(104, 'esp32_1', 30, '2026-03-12 13:23:29'),
(105, 'esp32_1', 30, '2026-03-12 13:23:29'),
(106, 'esp32_1', 30, '2026-03-12 13:23:30'),
(107, 'esp32_1', 30, '2026-03-12 13:29:28'),
(108, 'esp32_1', 30, '2026-03-12 13:30:06'),
(109, 'esp32_1', 30, '2026-03-12 13:30:10'),
(110, 'esp32_1', 30, '2026-03-12 13:30:15'),
(111, 'esp32_1', 30, '2026-03-12 13:30:20'),
(112, 'esp32_1', 30, '2026-03-12 13:30:24'),
(113, 'esp32_1', 100, '2026-03-12 13:31:28'),
(114, 'esp32_1', 100, '2026-03-12 13:31:34'),
(115, 'esp32_1', 60, '2026-03-12 13:49:42'),
(116, 'esp32_1', 60, '2026-03-12 13:49:51'),
(117, 'esp32_1', 100, '2026-03-12 13:49:52'),
(118, 'esp32_1', 100, '2026-03-12 13:49:58'),
(119, 'esp32_1', 30, '2026-03-14 05:08:00'),
(120, 'esp32_1', 100, '2026-03-14 05:08:09'),
(121, 'esp32_1', 30, '2026-03-14 05:49:32'),
(122, 'esp32_1', 30, '2026-03-14 05:55:54'),
(123, 'esp32_1', 100, '2026-03-14 05:56:04'),
(124, 'esp32_1', 100, '2026-03-14 05:56:08'),
(125, 'esp32_1', 60, '2026-03-14 05:56:09'),
(126, 'esp32_1', 30, '2026-03-14 05:56:12'),
(127, 'esp32_1', 100, '2026-03-14 05:56:19'),
(128, 'esp32_1', 60, '2026-03-14 05:56:25'),
(129, 'esp32_1', 100, '2026-03-14 05:56:36'),
(130, 'esp32_1', 100, '2026-03-14 05:56:41'),
(131, 'esp32_1', 60, '2026-03-14 05:56:44'),
(132, 'esp32_1', 60, '2026-03-14 05:56:46'),
(133, 'esp32_1', 30, '2026-03-14 05:56:53'),
(134, 'esp32_1', 30, '2026-03-14 05:56:57'),
(135, 'esp32_1', 100, '2026-03-14 05:57:00'),
(136, 'esp32_1', 100, '2026-03-14 05:57:07'),
(137, 'esp32_1', 60, '2026-03-14 05:57:15'),
(138, 'esp32_1', 60, '2026-03-14 05:57:19'),
(139, 'esp32_1', 100, '2026-03-14 05:57:35'),
(140, 'esp32_1', 100, '2026-03-14 05:57:41'),
(141, 'esp32_1', 30, '2026-03-14 05:57:58'),
(142, 'esp32_1', 30, '2026-03-14 05:57:59'),
(143, 'esp32_1', 30, '2026-03-14 05:58:00'),
(144, 'esp32_1', 30, '2026-03-14 05:58:02'),
(145, 'esp32_1', 30, '2026-03-14 05:58:05'),
(146, 'esp32_1', 100, '2026-03-14 05:58:06'),
(147, 'esp32_1', 30, '2026-03-14 05:58:09'),
(148, 'esp32_1', 100, '2026-03-14 05:58:16'),
(149, 'esp32_1', 100, '2026-03-14 05:58:20'),
(150, 'esp32_1', 100, '2026-03-14 05:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `found_pet_reports`
--

CREATE TABLE `found_pet_reports` (
  `id` int(11) NOT NULL,
  `alert_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `found_location` varchar(255) NOT NULL,
  `found_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `general_found_pets`
--

CREATE TABLE `general_found_pets` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `pet_type` varchar(50) DEFAULT NULL,
  `pet_breed` varchar(100) DEFAULT NULL,
  `found_location` varchar(255) NOT NULL,
  `found_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `pet_image` text DEFAULT NULL,
  `status` enum('Active','Resolved') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `record_type` varchar(100) NOT NULL,
  `record_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_reminders`
--

CREATE TABLE `health_reminders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_name` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `due_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','completed','deferred') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `health_reminders`
--

INSERT INTO `health_reminders` (`id`, `user_id`, `pet_name`, `title`, `message`, `due_at`, `status`, `created_at`) VALUES
(1, 1, 'Bella', 'Heartworm Medication', 'Needs her medication in 30 minutes', '2026-03-08 13:44:16', 'pending', '2026-03-08 13:14:16'),
(2, 2, NULL, 'Vaccination Reminder', NULL, '2026-03-09 15:43:49', 'pending', '2026-03-08 15:43:49');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 4.5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `name`, `address`, `image_url`, `contact_number`, `rating`, `created_at`) VALUES
(1, 'City Pet Clinic', '123 Pet Lane, Downtown', 'images/hosp1.png', NULL, 4.8, '2026-03-08 16:36:50'),
(2, 'Healthy Paws Hospital', '456 Fur Avenue, Westside', 'images/hosp2.png', NULL, 4.6, '2026-03-08 16:36:50'),
(3, 'Whiskers & Wag Clinic', '789 Tail Road, Eastside', 'images/hosp3.png', NULL, 4.9, '2026-03-08 16:36:50');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_services`
--

CREATE TABLE `hospital_services` (
  `id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `service_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_services`
--

INSERT INTO `hospital_services` (`id`, `hospital_id`, `service_name`, `price`, `description`) VALUES
(13, 1, 'General Checkup', 500.00, 'Professional General Checkup for your pet'),
(14, 1, 'Emergency Consultation', 500.00, 'Professional Emergency Consultation for your pet'),
(15, 1, 'Vaccination', 800.00, 'Professional Vaccination for your pet'),
(16, 1, 'Full Grooming', 500.00, 'Professional Full Grooming for your pet'),
(17, 1, 'Bath & Dry', 500.00, 'Professional Bath & Dry for your pet'),
(18, 2, 'General Checkup', 500.00, 'Professional General Checkup for your pet'),
(19, 2, 'Emergency Consultation', 500.00, 'Professional Emergency Consultation for your pet'),
(20, 2, 'Vaccination', 800.00, 'Professional Vaccination for your pet'),
(21, 2, 'Full Grooming', 500.00, 'Professional Full Grooming for your pet'),
(22, 2, 'Bath & Dry', 500.00, 'Professional Bath & Dry for your pet'),
(23, 3, 'General Checkup', 500.00, 'Professional General Checkup for your pet'),
(24, 3, 'Emergency Consultation', 500.00, 'Professional Emergency Consultation for your pet'),
(25, 3, 'Vaccination', 800.00, 'Professional Vaccination for your pet'),
(26, 3, 'Full Grooming', 500.00, 'Professional Full Grooming for your pet'),
(27, 3, 'Bath & Dry', 500.00, 'Professional Bath & Dry for your pet');

-- --------------------------------------------------------

--
-- Table structure for table `lost_pet_alerts`
--

CREATE TABLE `lost_pet_alerts` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_seen_location` varchar(255) NOT NULL,
  `last_seen_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Resolved') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Processing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `payment_id`, `total_amount`, `shipping_address`, `city`, `zip_code`, `status`, `created_at`, `payment_method`, `address`, `phone`) VALUES
(1, 2, 'pay_SOnq7tR79n3h1h', 2499.00, 'thyparambil house kanjrapply', 'kottayam', '10052', 'Processing', '2026-03-08 16:48:40', NULL, NULL, NULL),
(2, 2, 'MOB_PD2ZDDQ28', 399.00, 'GM, GM, 255563', 'GM', '255563', 'Processing', '2026-03-14 06:00:03', 'Netbanking (SBI)', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(1, 1, 1, 1, 2499.00),
(2, 2, 7, 1, 399.00);

-- --------------------------------------------------------

--
-- Table structure for table `pet_memories`
--

CREATE TABLE `pet_memories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `memory_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_rehoming_listings`
--

CREATE TABLE `pet_rehoming_listings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_type_id` int(11) NOT NULL,
  `breed_id` int(11) DEFAULT NULL,
  `pet_name` varchar(100) NOT NULL,
  `age_years` int(11) DEFAULT NULL,
  `age_months` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Unknown') NOT NULL,
  `size` enum('Small','Medium','Large','Extra Large') DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `is_vaccinated` tinyint(1) DEFAULT 0,
  `is_neutered` tinyint(1) DEFAULT 0,
  `health_status` text DEFAULT NULL,
  `temperament` text DEFAULT NULL,
  `special_needs` text DEFAULT NULL,
  `reason_for_rehoming` text NOT NULL,
  `adoption_fee` decimal(10,2) DEFAULT 0.00,
  `location` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `primary_image` varchar(255) DEFAULT NULL,
  `additional_images` text DEFAULT NULL,
  `status` enum('Pending','Approved','Adopted','Rejected','Withdrawn') DEFAULT 'Pending',
  `views_count` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `approved_at` timestamp NULL DEFAULT NULL,
  `adopted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_types`
--

CREATE TABLE `pet_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pet_types`
--

INSERT INTO `pet_types` (`id`, `name`, `icon`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Dog', 'fa-dog', 1, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(2, 'Cat', 'fa-cat', 2, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(3, 'Bird', 'fa-dove', 3, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(4, 'Rabbit', 'fa-rabbit', 4, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(5, 'Fish', 'fa-fish', 5, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(6, 'Hamster', 'fa-hamster', 6, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18'),
(7, 'Other', 'fa-paw', 7, 1, '2026-03-08 13:14:18', '2026-03-08 13:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `stock` int(11) DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  `discount` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `shop_id`, `name`, `description`, `price`, `category`, `image_url`, `stock`, `created_at`, `status`, `discount`) VALUES
(1, 0, 'Premium Dog Food', 'Premium Chicken & Rice - Large Breed (3kg)', 2499.00, 'Food', 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=600', 100, '2026-03-08 13:14:17', 'active', 0),
(2, 0, 'Puppy Food', 'Healthy Growth Formula - Chicken & Milk (3kg)', 849.00, 'Food', 'https://images.unsplash.com/photo-1589924691195-41432c84c161?w=600', 100, '2026-03-08 13:14:17', 'active', 0),
(3, 0, 'Interactive Cat Toy', 'Smart Laser & Motion Sensor Toy', 499.00, 'Toys', 'https://images.unsplash.com/photo-1545249390-6bdfa286032f?w=600', 100, '2026-03-08 13:14:17', 'active', 0),
(4, 0, 'Comfort Pet Bed', 'Orthopedic Foam Pet Bed - Washable', 2899.00, 'Accessories', 'https://images.unsplash.com/photo-1591584250171-04144f87da1e?w=600', 100, '2026-03-08 13:14:17', 'active', 0),
(5, 0, 'Bird Seed Mix', 'Premium Mix Seeds for Small/Medium Birds (1kg)', 349.00, 'Food', 'https://images.unsplash.com/photo-1551969014-7d2c4da3d4f7?w=600', 100, '2026-03-08 13:14:17', 'active', 0),
(6, 0, 'Chew Bone', 'Durable Rubber Chew Bone (Medium)', 199.00, 'Toys', 'https://images.unsplash.com/photo-1544568100-847a948585b9?w=600', 100, '2026-03-08 13:14:17', 'active', 0),
(7, 0, 'Pet Vitamin Supplements', 'Multivitamin Soft Chews (60 count)', 399.00, 'Health', 'https://images.unsplash.com/photo-1583336663277-620dd17319e3?w=600', 100, '2026-03-08 13:14:17', 'active', 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `reply`, `created_at`) VALUES
(1, 2, 1, 5, 'Absolutely love this! My pet is so happy.', 'Thank you so much! We aim to please.', '2026-03-08 17:05:50'),
(2, 2, 1, 4, 'Really good quality, took a little long to ship though.', 'Sorry for the delay! We had a rush this week.', '2026-03-08 17:05:50'),
(3, 2, 1, 5, 'Best product on PetCloud. Highly recommended.', NULL, '2026-03-08 17:05:50');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `default_duration_minutes` int(11) DEFAULT 30,
  `is_medical` tinyint(1) DEFAULT 0,
  `is_home_service_supported` tinyint(1) DEFAULT 0,
  `is_clinic_service_supported` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category_id`, `name`, `description`, `default_duration_minutes`, `is_medical`, `is_home_service_supported`, `is_clinic_service_supported`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 1, 'General Checkup', NULL, 20, 1, 0, 1, 1, 0, '2026-03-08 13:14:17'),
(2, 1, 'Emergency Consultation', NULL, 30, 1, 0, 1, 1, 0, '2026-03-08 13:14:17'),
(3, 2, 'Vaccination', NULL, 15, 1, 0, 1, 1, 0, '2026-03-08 13:14:17'),
(4, 3, 'Full Grooming', NULL, 90, 0, 0, 1, 1, 0, '2026-03-08 13:14:17'),
(5, 3, 'Bath & Dry', NULL, 45, 0, 0, 1, 1, 0, '2026-03-08 13:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `slug`, `icon`, `description`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Medical Consultation', 'medical', 'fa-user-md', NULL, 1, 1, '2026-03-08 13:14:17'),
(2, 'Preventive Care', 'preventive', 'fa-shield-virus', NULL, 2, 1, '2026-03-08 13:14:17'),
(3, 'Grooming & Spa', 'grooming', 'fa-pump-soap', NULL, 3, 1, '2026-03-08 13:14:17'),
(4, 'Diagnostics', 'diagnostics', 'fa-microscope', NULL, 4, 1, '2026-03-08 13:14:17'),
(5, 'Surgery & Dental', 'surgery', 'fa-syringe', NULL, 5, 1, '2026-03-08 13:14:17'),
(6, 'Alternative Therapy', 'therapy', 'fa-spa', NULL, 6, 1, '2026-03-08 13:14:17'),
(7, 'Training & Behavior', 'training', 'fa-graduation-cap', NULL, 7, 1, '2026-03-08 13:14:17'),
(8, 'Boarding & Daycare', 'boarding', 'fa-home', NULL, 8, 1, '2026-03-08 13:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `shop_applications`
--

CREATE TABLE `shop_applications` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  `shop_category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `business_reg` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `years_in_business` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shop_applications`
--

INSERT INTO `shop_applications` (`id`, `full_name`, `email`, `shop_name`, `shop_category`, `description`, `status`, `applied_at`, `phone`, `password_hash`, `business_reg`, `address`, `years_in_business`) VALUES
(1, 'alphuuu', 'alphu@gmail.com', 'alphu shop', 'All Categories', 'selling my prodects', 'approved', '2026-03-08 14:56:15', '6232659845', '$2y$10$QjjpVXblfoK3ILZ2RO1YiunR2vJt/V9sv1fJWNPFeUbczfi6Yynoy', '1234567890', 'thyparambil house', 3);

-- --------------------------------------------------------

--
-- Table structure for table `shop_notifications`
--

CREATE TABLE `shop_notifications` (
  `id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop_notifications`
--

INSERT INTO `shop_notifications` (`id`, `shop_id`, `title`, `message`, `is_read`, `type`, `created_at`) VALUES
(1, 1, 'Welcome aboard!', 'Your shop is now active. Start adding products to get orders!', 0, NULL, '2026-03-08 15:42:50');

-- --------------------------------------------------------

--
-- Table structure for table `smart_feeder_schedules`
--

CREATE TABLE `smart_feeder_schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `feeding_time` time NOT NULL,
  `quantity_grams` int(11) NOT NULL,
  `mode` enum('Automatic','Manual') DEFAULT 'Automatic',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `frequency` varchar(50) DEFAULT 'Daily'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `smart_feeder_schedules`
--

INSERT INTO `smart_feeder_schedules` (`id`, `user_id`, `pet_id`, `feeding_time`, `quantity_grams`, `mode`, `status`, `created_at`, `frequency`) VALUES
(1, 2, 3, '19:14:00', 40, 'Automatic', 'Active', '2026-03-12 13:43:14', 'Daily'),
(2, 2, 3, '19:15:00', 100, 'Automatic', 'Active', '2026-03-12 13:43:32', 'Daily'),
(3, 2, 3, '19:18:00', 40, 'Automatic', 'Active', '2026-03-12 13:48:25', 'Daily');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `last_updated`) VALUES
(1, 'commission_rate', '10', 'Platform commission percentage for marketplace orders', '2026-03-08 17:14:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(500) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'client',
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `google_id`, `profile_pic`, `role`, `location`, `created_at`) VALUES
(1, 'System Administrator', 'admin@gmail.com', '$2y$10$u2hXwgUnqoznoP225M2Ccu2NrJfLuUjxyfNZSvxiPUFVKUK1N8It.', NULL, NULL, 'admin', NULL, '2026-03-08 13:26:41'),
(2, 'Joshua Joseph', 'joshuajoseph10310@gmail.com', '$2y$10$t9UPLs9O9zaPPVWQrJ2b3e2UcoKB.5cRsiysezWndfUoIHe0ZpseC', NULL, NULL, 'client', NULL, '2026-03-08 13:26:41'),
(3, 'joshua jj', 'joshuajoseph10cfc@gmail.com', '$2y$10$yL7gW38sFBXei6sF99DSc.axc2Bfc4MyFcxHpebuJLTztZiLj39yG', NULL, NULL, 'client', NULL, '2026-03-08 13:29:58'),
(4, 'alphuuu', 'alphu@gmail.com', '$2y$10$rJqsVIxwu4XqiGJTbkDOWOdC260jFMWr3PTeE/vD9ar9A5XZLDkjy', NULL, NULL, 'shop_owner', NULL, '2026-03-08 14:56:38'),
(5, 'Test User uhij9', 'test_uhij9@example.com', '$2y$10$lFrda4ASXznSR8bB6tebeeBZj5qzO20UrXyQQ/psXE/SP2qni68/u', NULL, NULL, 'client', NULL, '2026-03-09 10:02:23'),
(6, 'Pet Owner l2cn3', 'owner_l2cn3@petcloud.com', '$2y$10$/cPhusndIcaYrRA.7VpDqu5TnGOsUHSVX2oDh1hM9uhkSoev1ziyC', NULL, NULL, 'client', NULL, '2026-03-11 11:04:46'),
(7, 'Pet Owner zlg7c', 'owner_zlg7c@petcloud.com', '$2y$10$aa.l4J5v162pdSaD7Cvfk.7DlOOCOADMeDt5ZxY7iGRl9hRaUu7VG', NULL, NULL, 'client', NULL, '2026-03-11 11:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `user_pets`
--

CREATE TABLE `user_pets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `pet_breed` varchar(100) DEFAULT NULL,
  `pet_age` varchar(50) DEFAULT NULL,
  `pet_type` varchar(50) DEFAULT NULL,
  `pet_image` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Lost') DEFAULT 'Active',
  `pet_gender` varchar(20) DEFAULT 'Unknown',
  `pet_weight` varchar(20) DEFAULT '0 kg',
  `pet_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_pets`
--

INSERT INTO `user_pets` (`id`, `user_id`, `pet_name`, `pet_breed`, `pet_age`, `pet_type`, `pet_image`, `created_at`, `status`, `pet_gender`, `pet_weight`, `pet_description`) VALUES
(3, 2, 'enzo', 'spits', '5 months', 'Dog', 'images/uploads/pets/pet_69ad889e52feb.png', '2026-03-08 14:33:02', 'Active', 'Male', '17 kg', 'i am go to abroad');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_adoption_listing` (`listing_id`);

--
-- Indexes for table `adoption_breeds`
--
ALTER TABLE `adoption_breeds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `adoption_inquiries`
--
ALTER TABLE `adoption_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_listing` (`listing_id`),
  ADD KEY `idx_inquirer` (`inquirer_user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `adoption_listings`
--
ALTER TABLE `adoption_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `adoption_pet_types`
--
ALTER TABLE `adoption_pet_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_breed_per_type` (`pet_type_id`,`name`),
  ADD KEY `idx_pet_type` (`pet_type_id`),
  ADD KEY `idx_breed_group` (`breed_group_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_composite` (`pet_type_id`,`breed_group_id`,`is_active`);

--
-- Indexes for table `breed_categories`
--
ALTER TABLE `breed_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_type_id` (`pet_type_id`);

--
-- Indexes for table `breed_groups`
--
ALTER TABLE `breed_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `daily_tasks`
--
ALTER TABLE `daily_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feeding_history`
--
ALTER TABLE `feeding_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feeding_logs`
--
ALTER TABLE `feeding_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `feeding_schedules`
--
ALTER TABLE `feeding_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feed_commands`
--
ALTER TABLE `feed_commands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feed_logs`
--
ALTER TABLE `feed_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `found_pet_reports`
--
ALTER TABLE `found_pet_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alert_id` (`alert_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `general_found_pets`
--
ALTER TABLE `general_found_pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `health_reminders`
--
ALTER TABLE `health_reminders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hospital_services`
--
ALTER TABLE `hospital_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hospital_id` (`hospital_id`,`service_name`);

--
-- Indexes for table `lost_pet_alerts`
--
ALTER TABLE `lost_pet_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `pet_memories`
--
ALTER TABLE `pet_memories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pet_rehoming_listings`
--
ALTER TABLE `pet_rehoming_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_pet_type` (`pet_type_id`),
  ADD KEY `idx_breed` (`breed_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`city`,`state`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_filter_composite` (`status`,`pet_type_id`,`breed_id`,`city`);

--
-- Indexes for table `pet_types`
--
ALTER TABLE `pet_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `shop_applications`
--
ALTER TABLE `shop_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop_notifications`
--
ALTER TABLE `shop_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smart_feeder_schedules`
--
ALTER TABLE `smart_feeder_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`);

--
-- Indexes for table `user_pets`
--
ALTER TABLE `user_pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `adoption_breeds`
--
ALTER TABLE `adoption_breeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `adoption_inquiries`
--
ALTER TABLE `adoption_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adoption_listings`
--
ALTER TABLE `adoption_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `adoption_pet_types`
--
ALTER TABLE `adoption_pet_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `breed_categories`
--
ALTER TABLE `breed_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `breed_groups`
--
ALTER TABLE `breed_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `daily_tasks`
--
ALTER TABLE `daily_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feeding_history`
--
ALTER TABLE `feeding_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feeding_logs`
--
ALTER TABLE `feeding_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `feeding_schedules`
--
ALTER TABLE `feeding_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feed_commands`
--
ALTER TABLE `feed_commands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `feed_logs`
--
ALTER TABLE `feed_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `found_pet_reports`
--
ALTER TABLE `found_pet_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_found_pets`
--
ALTER TABLE `general_found_pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_reminders`
--
ALTER TABLE `health_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hospital_services`
--
ALTER TABLE `hospital_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `lost_pet_alerts`
--
ALTER TABLE `lost_pet_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pet_memories`
--
ALTER TABLE `pet_memories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_rehoming_listings`
--
ALTER TABLE `pet_rehoming_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_types`
--
ALTER TABLE `pet_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shop_applications`
--
ALTER TABLE `shop_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shop_notifications`
--
ALTER TABLE `shop_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `smart_feeder_schedules`
--
ALTER TABLE `smart_feeder_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_pets`
--
ALTER TABLE `user_pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD CONSTRAINT `adoption_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_adoption_listing` FOREIGN KEY (`listing_id`) REFERENCES `adoption_listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `adoption_breeds`
--
ALTER TABLE `adoption_breeds`
  ADD CONSTRAINT `adoption_breeds_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `breed_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `adoption_inquiries`
--
ALTER TABLE `adoption_inquiries`
  ADD CONSTRAINT `adoption_inquiries_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `pet_rehoming_listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `adoption_listings`
--
ALTER TABLE `adoption_listings`
  ADD CONSTRAINT `adoption_listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `breeds`
--
ALTER TABLE `breeds`
  ADD CONSTRAINT `breeds_ibfk_1` FOREIGN KEY (`pet_type_id`) REFERENCES `pet_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `breeds_ibfk_2` FOREIGN KEY (`breed_group_id`) REFERENCES `breed_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `breed_categories`
--
ALTER TABLE `breed_categories`
  ADD CONSTRAINT `breed_categories_ibfk_1` FOREIGN KEY (`pet_type_id`) REFERENCES `adoption_pet_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `feeding_history`
--
ALTER TABLE `feeding_history`
  ADD CONSTRAINT `feeding_history_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `feeding_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feeding_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feeding_logs`
--
ALTER TABLE `feeding_logs`
  ADD CONSTRAINT `feeding_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feeding_logs_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `user_pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feeding_schedules`
--
ALTER TABLE `feeding_schedules`
  ADD CONSTRAINT `feeding_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `found_pet_reports`
--
ALTER TABLE `found_pet_reports`
  ADD CONSTRAINT `found_pet_reports_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `lost_pet_alerts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `found_pet_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `general_found_pets`
--
ALTER TABLE `general_found_pets`
  ADD CONSTRAINT `general_found_pets_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hospital_services`
--
ALTER TABLE `hospital_services`
  ADD CONSTRAINT `hospital_services_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_pet_alerts`
--
ALTER TABLE `lost_pet_alerts`
  ADD CONSTRAINT `lost_pet_alerts_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `user_pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lost_pet_alerts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `pet_rehoming_listings`
--
ALTER TABLE `pet_rehoming_listings`
  ADD CONSTRAINT `pet_rehoming_listings_ibfk_1` FOREIGN KEY (`pet_type_id`) REFERENCES `pet_types` (`id`),
  ADD CONSTRAINT `pet_rehoming_listings_ibfk_2` FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `smart_feeder_schedules`
--
ALTER TABLE `smart_feeder_schedules`
  ADD CONSTRAINT `smart_feeder_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `smart_feeder_schedules_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `user_pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_pets`
--
ALTER TABLE `user_pets`
  ADD CONSTRAINT `user_pets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
