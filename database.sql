-- Database Export for Waste Monitoring App
-- Combined from Migrations and Seeders

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_username_is_active_index` (`username`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: password_resets
-- --------------------------------------------------------

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: failed_jobs
-- --------------------------------------------------------

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: districts
-- --------------------------------------------------------

CREATE TABLE `districts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `boundaries` json NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3b82f6',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `districts_is_active_index` (`is_active`),
  KEY `districts_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: permissions
-- --------------------------------------------------------

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: roles
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: model_has_permissions
-- --------------------------------------------------------

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: model_has_roles
-- --------------------------------------------------------

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: role_has_permissions
-- --------------------------------------------------------

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: waste_types
-- --------------------------------------------------------

CREATE TABLE `waste_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#28a745',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `waste_types_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: waste_reports
-- --------------------------------------------------------

CREATE TABLE `waste_reports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `waste_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `image_feedback` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('pending','processed','completed','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `waste_reports_user_id_status_index` (`user_id`,`status`),
  KEY `waste_reports_latitude_longitude_index` (`latitude`,`longitude`),
  KEY `waste_reports_created_at_index` (`created_at`),
  KEY `waste_reports_waste_type_id_foreign` (`waste_type_id`),
  KEY `waste_reports_district_id_index` (`district_id`),
  CONSTRAINT `waste_reports_district_id_locked` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `waste_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `waste_reports_waste_type_id_foreign` FOREIGN KEY (`waste_type_id`) REFERENCES `waste_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- DATA SEEDING
-- --------------------------------------------------------

-- Roles
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'user', 'web', NOW(), NOW()),
(2, 'admin', 'web', NOW(), NOW());

-- Permissions
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view waste reports', 'web', NOW(), NOW()),
(2, 'create waste reports', 'web', NOW(), NOW()),
(3, 'edit waste reports', 'web', NOW(), NOW()),
(4, 'delete waste reports', 'web', NOW(), NOW()),
(5, 'approve waste reports', 'web', NOW(), NOW()),
(6, 'reject waste reports', 'web', NOW(), NOW()),
(7, 'view users', 'web', NOW(), NOW()),
(8, 'create users', 'web', NOW(), NOW()),
(9, 'edit users', 'web', NOW(), NOW()),
(10, 'delete users', 'web', NOW(), NOW()),
(11, 'manage roles', 'web', NOW(), NOW()),
(12, 'view waste types', 'web', NOW(), NOW()),
(13, 'create waste types', 'web', NOW(), NOW()),
(14, 'edit waste types', 'web', NOW(), NOW()),
(15, 'delete waste types', 'web', NOW(), NOW()),
(16, 'view dashboard', 'web', NOW(), NOW()),
(17, 'view admin dashboard', 'web', NOW(), NOW()),
(18, 'view user dashboard', 'web', NOW(), NOW()),
(19, 'view map', 'web', NOW(), NOW()),
(20, 'add location', 'web', NOW(), NOW());

-- Role Has Permissions (Mapping)
-- Admin gets all (1-20)
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 2), (2, 2), (3, 2), (4, 2), (5, 2), (6, 2), (7, 2), (8, 2), (9, 2), (10, 2),
(11, 2), (12, 2), (13, 2), (14, 2), (15, 2), (16, 2), (17, 2), (18, 2), (19, 2), (20, 2);

-- User gets specific permissions
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1), -- view waste reports
(2, 1), -- create waste reports
(3, 1), -- edit waste reports
(12, 1), -- view waste types
(16, 1), -- view dashboard
(18, 1), -- view user dashboard
(19, 1), -- view map
(20, 1); -- add location

-- Waste Types
INSERT INTO `waste_types` (`id`, `name`, `description`, `icon`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sampah Organik', 'Sampah yang berasal dari makhluk hidup seperti sisa makanan, daun, dll', 'fas fa-leaf', '#28a745', 1, NOW(), NOW()),
(2, 'Sampah Anorganik', 'Sampah yang tidak dapat terurai seperti plastik, kaca, logam', 'fas fa-recycle', '#007bff', 1, NOW(), NOW()),
(3, 'Sampah B3', 'Sampah berbahaya dan beracun seperti baterai, obat-obatan', 'fas fa-exclamation-triangle', '#dc3545', 1, NOW(), NOW()),
(4, 'Sampah Elektronik', 'Sampah peralatan elektronik seperti HP, laptop, TV', 'fas fa-laptop', '#6f42c1', 1, NOW(), NOW()),
(5, 'Sampah Konstruksi', 'Sampah dari bangunan seperti batu, semen, kayu', 'fas fa-hammer', '#fd7e14', 1, NOW(), NOW()),
(6, 'Sampah Lainnya', 'Jenis sampah lainnya yang tidak termasuk kategori di atas', 'fas fa-trash', '#6c757d', 1, NOW(), NOW());

-- Users (Passwords are: admin123 and user123 hashed)
INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `phone`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', 'admin@wasteapp.com', NOW(), '$2y$10$5FxkFxDlGcvIwDaFIyognuAuIw7UXyv4Kupvou9EVIiB2uR1GDl4C', '081234567890', 'Jl. Admin No. 1', 1, NOW(), NOW()),
(2, 'User Demo', 'user', 'user@wasteapp.com', NOW(), '$2y$10$TTCZ87D8ioBRhEoUfe58juOVP/SAdu5jkUFp/YXLopURrJBQhX93y', '081234567891', 'Jl. User No. 1', 1, NOW(), NOW());

-- Model Has Roles (Mapping)
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(2, 'App\\User', 1), -- Admin
(1, 'App\\User', 2); -- User Demo

COMMIT;
