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

-- =============================================================================
-- BAHAGIAN A: role enum — perlu jika migrate belum jalan
-- =============================================================================
ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('superadmin','surveyor','engineer','ta','director') NOT NULL DEFAULT 'engineer';

-- =============================================================================
-- BAHAGIAN B: 4 UNIT AKTIF + USER (selamat diulang)
-- =============================================================================

INSERT INTO `units` (`name`, `code`, `description`, `parent_id`, `status`, `sort_order`, `created_at`, `updated_at`)
VALUES
  ('Saliran & Cerun', 'SAL-CERUN', 'Unit Saliran & Cerun — Sinkhole dan Cerun Runtuh', NULL, 'active', 1, NOW(), NOW()),
  ('Jalan', 'JLN', 'Unit Jalan — Engineering', NULL, 'active', 2, NOW(), NOW()),
  ('Structure', 'STR', 'Unit Structure — Engineering', NULL, 'active', 3, NOW(), NOW()),
  ('M&E', 'ME', 'Unit M&E (Mekanikal & Elektrik) — Engineering', NULL, 'active', 4, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `status` = 'active',
  `sort_order` = VALUES(`sort_order`),
  `updated_at` = NOW();

-- Soft-deactivate legacy units (keep history)
UPDATE `units`
SET `status` = 'inactive', `updated_at` = NOW()
WHERE `code` NOT IN ('SAL-CERUN', 'JLN', 'STR', 'ME');

SET @pwd := '$2y$10$.f8ZLkmYGI7I8sA43KaGbu/T6Uiw//XkbgeDCfWuArcbm4zXpxkfm';

SET @sc  := (SELECT id FROM units WHERE code = 'SAL-CERUN' LIMIT 1);
SET @jln := (SELECT id FROM units WHERE code = 'JLN' LIMIT 1);
SET @str := (SELECT id FROM units WHERE code = 'STR' LIMIT 1);
SET @me  := (SELECT id FROM units WHERE code = 'ME' LIMIT 1);

-- Remap users/reports from legacy units
UPDATE users u
JOIN units old ON old.id = u.unit_id
SET u.unit_id = @sc
WHERE old.code IN ('SLR', 'CRN') AND @sc IS NOT NULL;

UPDATE users u
JOIN units old ON old.id = u.unit_id
SET u.unit_id = @me
WHERE old.code IN ('ELK', 'MEK') AND @me IS NOT NULL;

UPDATE reports r
JOIN units old ON old.id = r.unit_id
SET r.unit_id = @sc
WHERE old.code IN ('SLR', 'CRN') AND @sc IS NOT NULL;

UPDATE reports r
JOIN units old ON old.id = r.unit_id
SET r.unit_id = @me
WHERE old.code IN ('ELK', 'MEK') AND @me IS NOT NULL;

INSERT INTO `users` (`name`, `email`, `password`, `role`, `unit_id`, `phone`, `status`, `email_verified_at`, `created_at`, `updated_at`)
VALUES
  ('Super Admin', 'admin@mbsj.gov.my', @pwd, 'superadmin', NULL, '03-8000-0001', 'active', NOW(), NOW(), NOW()),
  ('Pengarah Kejuruteraan', 'director@mbsj.gov.my', @pwd, 'director', NULL, '03-8000-0099', 'active', NOW(), NOW(), NOW()),
  ('Ahmad Surveyor (Vendor)', 'surveyor@mbsj.gov.my', @pwd, 'surveyor', @jln, '012-1111001', 'active', NOW(), NOW(), NOW()),

  ('Ali TA (Jalan)', 'ta@mbsj.gov.my', @pwd, 'ta', @jln, '012-2222001', 'active', NOW(), NOW(), NOW()),
  ('Rahman Engineer (Jalan)', 'engineer@mbsj.gov.my', @pwd, 'engineer', @jln, '012-3333001', 'active', NOW(), NOW(), NOW()),

  ('Fatimah TA (Saliran & Cerun)', 'ta.saliran-cerun@mbsj.gov.my', @pwd, 'ta', @sc, '012-2222002', 'active', NOW(), NOW(), NOW()),
  ('Kumar Engineer (Saliran & Cerun)', 'engineer.saliran-cerun@mbsj.gov.my', @pwd, 'engineer', @sc, '012-3333002', 'active', NOW(), NOW(), NOW()),
  ('Surveyor Saliran & Cerun', 'surveyor.saliran-cerun@mbsj.gov.my', @pwd, 'surveyor', @sc, '012-1111002', 'active', NOW(), NOW(), NOW()),

  ('Wong TA (Structure)', 'ta.structure@mbsj.gov.my', @pwd, 'ta', @str, '012-2222003', 'active', NOW(), NOW(), NOW()),
  ('Azlan Engineer (Structure)', 'engineer.structure@mbsj.gov.my', @pwd, 'engineer', @str, '012-3333003', 'active', NOW(), NOW(), NOW()),

  ('Ravi TA (M&E)', 'ta.me@mbsj.gov.my', @pwd, 'ta', @me, '012-2222004', 'active', NOW(), NOW(), NOW()),
  ('Mei Engineer (M&E)', 'engineer.me@mbsj.gov.my', @pwd, 'engineer', @me, '012-3333004', 'active', NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `password` = VALUES(`password`),
  `role` = VALUES(`role`),
  `unit_id` = VALUES(`unit_id`),
  `phone` = VALUES(`phone`),
  `status` = 'active',
  `updated_at` = NOW();

-- Semakan: aktif units sahaja
SELECT id, code, name, status, sort_order FROM units ORDER BY status ASC, sort_order, name;

SELECT u.email, u.role, un.name AS unit_name, u.status
FROM users u
LEFT JOIN units un ON un.id = u.unit_id
WHERE u.email LIKE '%@mbsj.gov.my'
ORDER BY FIELD(u.role,'superadmin','director','surveyor','ta','engineer'), un.sort_order, u.email;
