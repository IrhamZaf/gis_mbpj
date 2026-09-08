-- =============================================================================
-- GIS MBSJ — SQL update untuk server (MySQL)
-- Password semua akaun demo: password
--
-- Fail: database/sql/server_update_units_users.sql
-- 1) Backup DB
-- 2) Prefer: php artisan migrate --force  (untuk skema)
-- 3) Jalankan BAHAGIAN B di bawah untuk unit + user
-- =============================================================================

SET NAMES utf8mb4;

-- =============================================================================
-- BAHAGIAN A0: CREATE TABLE units (wajib jika belum migrate)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text NULL,
  `parent_id` bigint unsigned NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_code_unique` (`code`),
  KEY `units_parent_id_foreign` (`parent_id`),
  CONSTRAINT `units_parent_id_foreign`
    FOREIGN KEY (`parent_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- users.unit_id + status (skip error jika kolum sudah wujud)
-- Uncomment & jalankan jika perlu:
-- ALTER TABLE `users` ADD COLUMN `unit_id` bigint unsigned NULL AFTER `role`;
-- ALTER TABLE `users` ADD CONSTRAINT `users_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `users` ADD COLUMN `status` enum('active','inactive') NOT NULL DEFAULT 'active' AFTER `phone`;

-- =============================================================================
-- BAHAGIAN A: role enum — perlu jika migrate belum jalan
-- =============================================================================
ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('superadmin','surveyor','engineer','ta','director') NOT NULL DEFAULT 'engineer';

-- =============================================================================
-- BAHAGIAN B: UNIT + USER (selamat diulang)
-- =============================================================================

INSERT INTO `units` (`name`, `code`, `description`, `parent_id`, `status`, `sort_order`, `created_at`, `updated_at`)
VALUES
  ('Jalan', 'JLN', 'Unit Jalan - Engineering', NULL, 'active', 1, NOW(), NOW()),
  ('Saliran', 'SLR', 'Unit Saliran - Engineering', NULL, 'active', 2, NOW(), NOW()),
  ('Struktur', 'STR', 'Unit Struktur - Engineering', NULL, 'active', 3, NOW(), NOW()),
  ('Elektrik', 'ELK', 'Unit Elektrik - Engineering', NULL, 'active', 4, NOW(), NOW()),
  ('Mekanikal', 'MEK', 'Unit Mekanikal - Engineering', NULL, 'active', 5, NOW(), NOW()),
  ('Infrastruktur', 'INF', 'Unit Infrastruktur - Engineering', NULL, 'active', 6, NOW(), NOW()),
  ('Cerun', 'CRN', 'Unit Cerun - Engineering', NULL, 'active', 7, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `status` = 'active',
  `sort_order` = VALUES(`sort_order`),
  `updated_at` = NOW();

SET @pwd := '$2y$10$.f8ZLkmYGI7I8sA43KaGbu/T6Uiw//XkbgeDCfWuArcbm4zXpxkfm';

SET @jln := (SELECT id FROM units WHERE code = 'JLN' LIMIT 1);
SET @slr := (SELECT id FROM units WHERE code = 'SLR' LIMIT 1);
SET @str := (SELECT id FROM units WHERE code = 'STR' LIMIT 1);
SET @elk := (SELECT id FROM units WHERE code = 'ELK' LIMIT 1);
SET @mek := (SELECT id FROM units WHERE code = 'MEK' LIMIT 1);
SET @inf := (SELECT id FROM units WHERE code = 'INF' LIMIT 1);
SET @crn := (SELECT id FROM units WHERE code = 'CRN' LIMIT 1);

-- Pastikan email unik (ON DUPLICATE KEY UPDATE bergantung unique pada email)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `unit_id`, `phone`, `status`, `email_verified_at`, `created_at`, `updated_at`)
VALUES
  ('Super Admin', 'admin@mbsj.gov.my', @pwd, 'superadmin', NULL, '03-8000-0001', 'active', NOW(), NOW(), NOW()),
  ('Pengarah Kejuruteraan', 'director@mbsj.gov.my', @pwd, 'director', NULL, '03-8000-0099', 'active', NOW(), NOW(), NOW()),
  ('Ahmad Surveyor (Vendor)', 'surveyor@mbsj.gov.my', @pwd, 'surveyor', @jln, '012-1111001', 'active', NOW(), NOW(), NOW()),

  ('Ali TA (Jalan)', 'ta@mbsj.gov.my', @pwd, 'ta', @jln, '012-2222001', 'active', NOW(), NOW(), NOW()),
  ('Rahman Engineer (Jalan)', 'engineer@mbsj.gov.my', @pwd, 'engineer', @jln, '012-3333001', 'active', NOW(), NOW(), NOW()),

  ('Fatimah TA (Saliran)', 'ta.saliran@mbsj.gov.my', @pwd, 'ta', @slr, '012-2222002', 'active', NOW(), NOW(), NOW()),
  ('Kumar Engineer (Saliran)', 'engineer.saliran@mbsj.gov.my', @pwd, 'engineer', @slr, '012-3333002', 'active', NOW(), NOW(), NOW()),

  ('Wong TA (Struktur)', 'ta.struktur@mbsj.gov.my', @pwd, 'ta', @str, '012-2222003', 'active', NOW(), NOW(), NOW()),
  ('Azlan Engineer (Struktur)', 'engineer.struktur@mbsj.gov.my', @pwd, 'engineer', @str, '012-3333003', 'active', NOW(), NOW(), NOW()),

  ('Ravi TA (Elektrik)', 'ta.elektrik@mbsj.gov.my', @pwd, 'ta', @elk, '012-2222004', 'active', NOW(), NOW(), NOW()),
  ('Mei Engineer (Elektrik)', 'engineer.elektrik@mbsj.gov.my', @pwd, 'engineer', @elk, '012-3333004', 'active', NOW(), NOW(), NOW()),

  ('Hassan TA (Mekanikal)', 'ta.mekanikal@mbsj.gov.my', @pwd, 'ta', @mek, '012-2222005', 'active', NOW(), NOW(), NOW()),
  ('Priya Engineer (Mekanikal)', 'engineer.mekanikal@mbsj.gov.my', @pwd, 'engineer', @mek, '012-3333005', 'active', NOW(), NOW(), NOW()),

  ('Diana TA (Infrastruktur)', 'ta.infrastruktur@mbsj.gov.my', @pwd, 'ta', @inf, '012-2222006', 'active', NOW(), NOW(), NOW()),
  ('Farid Engineer (Infrastruktur)', 'engineer.infrastruktur@mbsj.gov.my', @pwd, 'engineer', @inf, '012-3333006', 'active', NOW(), NOW(), NOW()),

  ('Nora TA (Cerun)', 'ta.cerun@mbsj.gov.my', @pwd, 'ta', @crn, '012-2222007', 'active', NOW(), NOW(), NOW()),
  ('Lim Engineer (Cerun)', 'engineer.cerun@mbsj.gov.my', @pwd, 'engineer', @crn, '012-3333007', 'active', NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `password` = VALUES(`password`),
  `role` = VALUES(`role`),
  `unit_id` = VALUES(`unit_id`),
  `phone` = VALUES(`phone`),
  `status` = 'active',
  `updated_at` = NOW();

-- Semakan
SELECT u.email, u.role, un.name AS unit_name, u.status
FROM users u
LEFT JOIN units un ON un.id = u.unit_id
WHERE u.email LIKE '%@mbsj.gov.my'
ORDER BY FIELD(u.role,'superadmin','director','surveyor','ta','engineer'), un.sort_order, u.email;
