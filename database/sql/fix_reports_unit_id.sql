-- =============================================================================
-- Fix: Unknown column 'unit_id' on reports (+ related columns)
-- Database: u894365806_gis_mbpj
-- Jalankan di phpMyAdmin selepas table `units` sudah wujud
-- =============================================================================

-- 1) users.unit_id + users.status (skip jika Duplicate column)
ALTER TABLE `users`
  ADD COLUMN `unit_id` bigint unsigned NULL AFTER `role`;

ALTER TABLE `users`
  ADD COLUMN `status` enum('active','inactive') NOT NULL DEFAULT 'active' AFTER `phone`;

ALTER TABLE `users`
  ADD CONSTRAINT `users_unit_id_foreign`
  FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('superadmin','surveyor','engineer','ta','director') NOT NULL DEFAULT 'engineer';

-- 2) reports.unit_id + review fields
ALTER TABLE `reports`
  ADD COLUMN `unit_id` bigint unsigned NULL AFTER `user_id`;

ALTER TABLE `reports`
  ADD COLUMN `review_note` text NULL AFTER `submitted_at`;

ALTER TABLE `reports`
  ADD COLUMN `reviewed_at` timestamp NULL DEFAULT NULL AFTER `review_note`;

ALTER TABLE `reports`
  ADD COLUMN `reviewed_by` bigint unsigned NULL AFTER `reviewed_at`;

ALTER TABLE `reports`
  ADD CONSTRAINT `reports_unit_id_foreign`
  FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

ALTER TABLE `reports`
  ADD CONSTRAINT `reports_reviewed_by_foreign`
  FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- 3) workflow fields on reports
ALTER TABLE `reports`
  ADD COLUMN `workflow_status` varchar(255) NULL AFTER `status`;

ALTER TABLE `reports`
  ADD COLUMN `file_number` varchar(255) NULL AFTER `report_number`;

ALTER TABLE `reports`
  ADD COLUMN `vendor_name` varchar(255) NULL AFTER `location_name`;

-- 4) simplify status enum (optional; skip jika error)
-- Jika status lama ada under_review/returned/approved, backfill dulu:
UPDATE `reports` SET `workflow_status` = 'pending_site_visit' WHERE `status` = 'submitted' AND `workflow_status` IS NULL;
UPDATE `reports` SET `status` = 'submitted', `workflow_status` = 'pending_engineer_verification' WHERE `status` = 'under_review';
UPDATE `reports` SET `status` = 'submitted', `workflow_status` = 'engineer_returned' WHERE `status` = 'returned';
UPDATE `reports` SET `status` = 'completed', `workflow_status` = 'approved' WHERE `status` = 'approved';
UPDATE `reports` SET `workflow_status` = 'approved' WHERE `status` = 'completed' AND `workflow_status` IS NULL;

ALTER TABLE `reports`
  MODIFY COLUMN `status` ENUM('draft','submitted','completed') NOT NULL DEFAULT 'draft';

-- 5) Backfill unit_id pada reports dari unit surveyor (Jalan = JLN) jika NULL
UPDATE `reports` r
JOIN `users` u ON u.id = r.user_id
SET r.unit_id = u.unit_id
WHERE r.unit_id IS NULL AND u.unit_id IS NOT NULL;

UPDATE `reports`
SET `unit_id` = (SELECT id FROM units WHERE code = 'JLN' LIMIT 1)
WHERE `unit_id` IS NULL;

-- Semakan
SHOW COLUMNS FROM `reports` LIKE 'unit_id';
SELECT id, report_number, unit_id, status, workflow_status FROM `reports` LIMIT 20;
