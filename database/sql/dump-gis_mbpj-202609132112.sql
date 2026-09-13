-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: gis_mbpj
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attachment_types`
--

DROP TABLE IF EXISTS `attachment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '1',
  `allowed_extensions` json NOT NULL,
  `max_size` int unsigned NOT NULL DEFAULT '51200',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attachment_types_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachment_types`
--

LOCK TABLES `attachment_types` WRITE;
/*!40000 ALTER TABLE `attachment_types` DISABLE KEYS */;
INSERT INTO `attachment_types` VALUES (1,'Main Report','MAIN_REPORT',1,'[\"pdf\", \"doc\", \"docx\", \"xls\", \"xlsx\", \"csv\", \"zip\", \"jpg\", \"jpeg\", \"png\", \"tif\", \"tiff\"]',51200,'2026-09-12 20:18:48','2026-09-12 20:18:48'),(2,'Report Borehole','BOREHOLE',1,'[\"pdf\", \"doc\", \"docx\", \"xls\", \"xlsx\", \"csv\", \"zip\", \"jpg\", \"jpeg\", \"png\", \"tif\", \"tiff\"]',51200,'2026-09-12 20:18:48','2026-09-12 20:18:48'),(3,'Data LiDAR','LIDAR',1,'[\"pdf\", \"doc\", \"docx\", \"xls\", \"xlsx\", \"csv\", \"zip\", \"jpg\", \"jpeg\", \"png\", \"tif\", \"tiff\", \"las\", \"laz\", \"geotiff\", \"xyz\", \"txt\"]',204800,'2026-09-12 20:18:48','2026-09-12 20:18:48'),(4,'Report UMAP','UMAP',1,'[\"pdf\", \"doc\", \"docx\", \"xls\", \"xlsx\", \"csv\", \"zip\", \"jpg\", \"jpeg\", \"png\", \"tif\", \"tiff\"]',51200,'2026-09-12 20:18:48','2026-09-12 20:18:48'),(5,'Report Seismic','SEISMIC',1,'[\"pdf\", \"doc\", \"docx\", \"xls\", \"xlsx\", \"csv\", \"zip\", \"jpg\", \"jpeg\", \"png\", \"tif\", \"tiff\"]',51200,'2026-09-12 20:18:48','2026-09-12 20:18:48');
/*!40000 ALTER TABLE `attachment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_attachment_types`
--

DROP TABLE IF EXISTS `category_attachment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category_attachment_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `attachment_type_id` bigint unsigned NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_att_type_unique` (`category_id`,`attachment_type_id`),
  KEY `category_attachment_types_attachment_type_id_foreign` (`attachment_type_id`),
  CONSTRAINT `category_attachment_types_attachment_type_id_foreign` FOREIGN KEY (`attachment_type_id`) REFERENCES `attachment_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_attachment_types_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `report_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_attachment_types`
--

LOCK TABLES `category_attachment_types` WRITE;
/*!40000 ALTER TABLE `category_attachment_types` DISABLE KEYS */;
INSERT INTO `category_attachment_types` VALUES (1,6,1,'Report Sinkhole',1,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(2,7,1,'Report Cerun Runtuh',1,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(3,6,2,'Report Borehole',2,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(4,7,2,'Report Borehole',2,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(5,6,3,'Data LiDAR',3,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(6,7,3,'Data LiDAR',3,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(7,6,4,'Report UMAP',4,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(8,7,4,'Report UMAP',4,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(9,6,5,'Report Seismic',5,'2026-09-12 20:18:48','2026-09-12 21:07:46'),(10,7,5,'Report Seismic',5,'2026-09-12 20:18:48','2026-09-12 21:07:46');
/*!40000 ALTER TABLE `category_attachment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `director_approvals`
--

DROP TABLE IF EXISTS `director_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `director_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint unsigned NOT NULL,
  `director_user_id` bigint unsigned NOT NULL,
  `decision` enum('approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `director_approvals_report_id_foreign` (`report_id`),
  KEY `director_approvals_director_user_id_foreign` (`director_user_id`),
  CONSTRAINT `director_approvals_director_user_id_foreign` FOREIGN KEY (`director_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `director_approvals_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `director_approvals`
--

LOCK TABLES `director_approvals` WRITE;
/*!40000 ALTER TABLE `director_approvals` DISABLE KEYS */;
INSERT INTO `director_approvals` VALUES (5,41,5,'approved','Diluluskan untuk rekod rasmi Jabatan Kejuruteraan.','Pengarah Kejuruteraan','Pengarah Kejuruteraan','2026-09-09 06:30:00','2026-09-12 20:31:05','2026-09-12 20:31:05'),(6,42,5,'rejected','Dokumen sokongan dan pelan tapak tidak mencukupi. Sila lengkapkan.','Pengarah Kejuruteraan','Pengarah Kejuruteraan','2026-09-09 20:31:05','2026-09-12 20:31:05','2026-09-12 20:31:05');
/*!40000 ALTER TABLE `director_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `engineer_verifications`
--

DROP TABLE IF EXISTS `engineer_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `engineer_verifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint unsigned NOT NULL,
  `engineer_user_id` bigint unsigned NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `decision` enum('verified','returned') COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `engineer_verifications_report_id_foreign` (`report_id`),
  KEY `engineer_verifications_engineer_user_id_foreign` (`engineer_user_id`),
  CONSTRAINT `engineer_verifications_engineer_user_id_foreign` FOREIGN KEY (`engineer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `engineer_verifications_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `engineer_verifications`
--

LOCK TABLES `engineer_verifications` WRITE;
/*!40000 ALTER TABLE `engineer_verifications` DISABLE KEYS */;
INSERT INTO `engineer_verifications` VALUES (9,39,3,'Sila kemaskini foto keadaan sebenar dan lengkapkan ukuran kedalaman retakan.','returned','Rahman Engineer (Jalan)','Jurutera','2026-09-11 20:31:05','2026-09-12 20:31:05','2026-09-12 20:31:05'),(10,40,3,'Kerja lapangan dan lawatan adalah konsisten. Disyorkan untuk diluluskan.','verified','Rahman Engineer (Jalan)','Jurutera','2026-09-12 01:10:00','2026-09-12 20:31:05','2026-09-12 20:31:05'),(11,41,3,'Disahkan lengkap. Tiada isu teknikal.','verified','Rahman Engineer (Jalan)','Jurutera','2026-09-07 20:31:05','2026-09-12 20:31:05','2026-09-12 20:31:05'),(12,42,3,'Disahkan untuk semakan Pengarah.','verified','Rahman Engineer (Jalan)','Jurutera','2026-09-08 20:31:05','2026-09-12 20:31:05','2026-09-12 20:31:05');
/*!40000 ALTER TABLE `engineer_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_05_17_000001_alter_users_add_role',1),(5,'2026_05_17_000002_create_report_categories_table',1),(6,'2026_05_17_000003_create_reports_table',1),(7,'2026_05_17_000004_create_report_attachments_table',1),(8,'2026_05_18_000001_add_survey_fields_to_report_attachments_table',1),(9,'2026_09_05_000001_create_units_table',2),(10,'2026_09_05_000002_add_unit_and_status_to_users_table',2),(11,'2026_09_05_000003_add_unit_and_expand_status_on_reports_table',2),(12,'2026_09_05_000004_seed_units_and_backfill_unit_id',2),(13,'2026_09_05_100001_expand_users_role_for_workflow',3),(14,'2026_09_05_100002_add_workflow_fields_to_reports_table',3),(15,'2026_09_05_100003_create_workflow_tables',3),(16,'2026_09_13_100001_restructure_units_saliran_cerun',4),(17,'2026_09_13_100002_add_unit_fields_to_report_categories',5),(18,'2026_09_13_100003_add_gis_fields_to_reports',5),(19,'2026_09_13_100004_create_attachment_types_and_versioning',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_attachments`
--

DROP TABLE IF EXISTS `report_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint unsigned NOT NULL,
  `attachment_type_id` bigint unsigned DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int unsigned NOT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `is_current` tinyint(1) NOT NULL DEFAULT '1',
  `document_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `parsed_data` json DEFAULT NULL,
  `parse_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `parse_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_attachments_report_id_foreign` (`report_id`),
  KEY `report_attachments_attachment_type_id_foreign` (`attachment_type_id`),
  KEY `report_attachments_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `report_attachments_attachment_type_id_foreign` FOREIGN KEY (`attachment_type_id`) REFERENCES `attachment_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `report_attachments_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_attachments`
--

LOCK TABLES `report_attachments` WRITE;
/*!40000 ALTER TABLE `report_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_categories`
--

DROP TABLE IF EXISTS `report_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_categories_slug_unique` (`slug`),
  KEY `report_categories_unit_id_foreign` (`unit_id`),
  CONSTRAINT `report_categories_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_categories`
--

LOCK TABLES `report_categories` WRITE;
/*!40000 ALTER TABLE `report_categories` DISABLE KEYS */;
INSERT INTO `report_categories` VALUES (1,NULL,'Sinkhole','sinkhole',NULL,'Laporan berkaitan sinkhole','active','2026-09-03 04:57:35','2026-09-03 04:57:35'),(2,NULL,'Cerun / Tanah Runtuh','cerun-tanah-runtuh',NULL,'Laporan berkaitan cerun dan tanah runtuh','active','2026-09-03 04:57:35','2026-09-03 04:57:35'),(3,NULL,'Utiliti Bawah Tanah','utiliti-bawah-tanah',NULL,'Laporan berkaitan utiliti bawah tanah','active','2026-09-03 04:57:35','2026-09-03 04:57:35'),(4,NULL,'Jalan Rosak','jalan-rosak',NULL,'Retakan, lubang dan kerosakan permukaan jalan','active','2026-09-05 05:25:33','2026-09-05 05:25:33'),(5,NULL,'Saliran Tertutup','saliran-tertutup',NULL,'Longkang / parit tersumbat','active','2026-09-05 05:25:33','2026-09-05 05:25:33'),(6,8,'Sinkhole','sinkhole-saliran-cerun','SINKHOLE','Laporan Sinkhole — Unit Saliran & Cerun','active','2026-09-12 20:18:48','2026-09-12 20:18:48'),(7,8,'Cerun Runtuh','cerun-runtuh-saliran-cerun','CERUN_RUNTUH','Laporan Cerun Runtuh — Unit Saliran & Cerun','active','2026-09-12 20:18:48','2026-09-12 20:18:48');
/*!40000 ALTER TABLE `report_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','submitted','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `workflow_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gps_accuracy` decimal(8,2) DEFAULT NULL,
  `vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gis_data` json DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reports_report_number_unique` (`report_number`),
  KEY `reports_category_id_foreign` (`category_id`),
  KEY `reports_user_id_foreign` (`user_id`),
  KEY `reports_unit_id_foreign` (`unit_id`),
  KEY `reports_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `reports_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `report_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reports_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,'RPT-DEMO-0001',NULL,1,2,1,'Sinkhole di Jalan SS2/24','Sinkhole berdiameter kira-kira 1.2m dikesan berhampiran longkang. Kawasan perlu diasingkan serta-merta.','submitted','site_visit_in_progress',3.1182000,101.6234000,'Persimpangan Jalan SS2/24, Subang Jaya',NULL,NULL,NULL,'{\"type\": \"FeatureCollection\", \"features\": [{\"type\": \"Feature\", \"geometry\": {\"type\": \"Polygon\", \"coordinates\": [[[101.6228, 3.1178], [101.624, 3.1178], [101.624, 3.1186], [101.6228, 3.1186], [101.6228, 3.1178]]]}, \"properties\": []}]}','2026-08-29 04:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-09 23:32:57'),(2,'RPT-DEMO-0002',NULL,2,2,1,'Cerun runtuh berhampiran Taman Jaya','Cerun tanah di lereng bukit menunjukkan retakan dan pergerakan tanah selepas hujan lebat.','submitted','pending_site_visit',3.1045000,101.6521000,'Taman Jaya, Subang Jaya',NULL,NULL,NULL,NULL,'2026-08-31 04:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-03 04:57:35'),(3,'RPT-DEMO-0003',NULL,3,2,1,'Kebocoran paip bawah tanah SS3','Air bertakung di jalan raya menunjukkan kemungkinan kebocoran paip utiliti bawah tanah.','submitted','pending_engineer_verification',3.0821000,101.6108000,'Jalan SS3/45, Subang Jaya',NULL,NULL,NULL,NULL,'2026-09-01 04:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-09 23:25:46'),(4,'RPT-DEMO-0004',NULL,1,2,1,'Lubang jalan di Seksyen 14','Lubang jalan sedalam 40cm di tengah lorong. Risiko kepada kenderaan dan penunggang motosikal.','submitted','pending_engineer_verification',3.1127000,101.6359000,'Jalan 14/29, Seksyen 14, PJ',NULL,NULL,NULL,NULL,'2026-09-02 04:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-09 23:57:45'),(5,'RPT-DEMO-0005',NULL,2,2,1,'Tanah runtuh di Bukit Gasing','Hakisan tanah di lereng bukit berhampiran laluan pendaki. Kawasan perlu dipantau.','submitted','pending_site_visit',3.0956000,101.6589000,'Bukit Gasing, Subang Jaya',NULL,NULL,NULL,NULL,'2026-09-02 16:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-03 04:57:35'),(6,'RPT-DEMO-0006',NULL,3,2,1,'Kabel utiliti terdedah di Kelana Jaya','Kabel utiliti terdedah akibat kerja penggalian tanpa penutupan semula yang betul.','submitted','site_visit_in_progress',3.1068000,101.5932000,'Lorong Kelana Jaya 1, Subang Jaya',NULL,NULL,NULL,NULL,'2026-09-02 22:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-09 23:26:50'),(7,'RPT-DEMO-0007',NULL,1,2,1,'Kemerosotan tanah di Damansara Utama','Kemerosotan tanah kecil dikesan berhampiran tapak binaan. Laporan awal untuk pemantauan.','draft',NULL,3.1354000,101.6215000,'Damansara Utama, Subang Jaya',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-03 04:57:35'),(8,'RPT-DEMO-0008',NULL,2,2,1,'Cerun tidak stabil di Ara Damansara','Cerun di tepi jalan menunjukkan tanda-tanda ketidakstabilan. Draf laporan untuk semakan lanjut.','draft',NULL,3.1289000,101.5784000,'Ara Damansara, Subang Jaya',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-03 04:57:35'),(9,'RPT-DEMO-0009',NULL,3,2,1,'Saluran paip rosak di Bandar Sunway','Saluran paip utama rosak menyebabkan air bertakung di kawasan komersial.','submitted','pending_site_visit',3.0738000,101.6065000,'Jalan PJS 11/15, Bandar Sunway',NULL,NULL,NULL,NULL,'2026-08-27 04:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-03 04:57:35'),(10,'RPT-DEMO-0010',NULL,1,2,1,'Retakan jalan di SS7','Retakan panjang di permukaan jalan sepanjang 15 meter. Perlu penilaian kejuruteraan.','submitted','pending_site_visit',3.1073000,101.6067000,'Jalan SS7/13, Kelana Jaya',NULL,NULL,NULL,NULL,'2026-09-02 04:57:35',NULL,NULL,NULL,'2026-09-03 04:57:35','2026-09-03 04:57:35'),(32,'RPT-WF-ELK-01','MBSJ/ENG/ELK/2026/501',3,2,9,'Lampu jalan rosak SS15 — Unit Elektrik','Beberapa tiang lampu jalan tidak berfungsi. Menunggu semakan TA Elektrik.','submitted','pending_site_visit',3.0741000,101.5861000,'Jalan SS15/2, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-04 22:31:35',NULL,NULL,NULL,'2026-09-05 22:31:35','2026-09-12 20:15:46'),(33,'RPT-WF-MEK-01',NULL,3,2,9,'Pam air rosak stesen sementara — Unit Mekanikal','Pam mekanikal di stesen sementara berbunyi abnormal. Draf surveyor.','draft',NULL,3.0605000,101.5955000,'USJ 5, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,NULL,NULL,NULL,NULL,'2026-09-05 22:31:35','2026-09-12 20:15:46'),(34,'RPT-WF-INF-01','MBSJ/ENG/INF/2026/701',4,2,6,'Kerosakan infrastruktur pejalan kaki SS19','Laluan pejalan kaki rosak. Menunggu pengesahan Engineer Infrastruktur.','submitted','pending_engineer_verification',3.0522000,101.5822000,'SS19, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-04 22:31:35',NULL,NULL,NULL,'2026-09-05 22:31:35','2026-09-05 22:31:35'),(35,'RPT-WF-DRAFT-01',NULL,4,2,1,'[DRAFT] Retakan jalan SS15 belum dihantar','Draf laporan retakan jalan sepanjang 8 meter di SS15. Belum lengkap lampiran.','draft',NULL,3.0735000,101.5852000,'Jalan SS15/4, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,NULL,NULL,NULL,NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(36,'RPT-WF-PENDING-VISIT-01','MBSJ/ENG/JLN/2026/101',1,2,1,'Sinkhole kecil di Jalan SS2/24','Sinkhole diameter ~0.8m berhampiran longkang. Perlu lawatan TA.','submitted','pending_site_visit',3.1182000,101.6234000,'Persimpangan Jalan SS2/24, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-11 20:31:05',NULL,NULL,NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(37,'RPT-WF-VISIT-PROGRESS-01','MBSJ/ENG/JLN/2026/102',4,2,1,'Lubang jalan di USJ 1 — lawatan sedang dijalankan','Lubang jalan sedalam 15cm di lorong kiri. TA sedang buat lawatan.','submitted','site_visit_in_progress',3.0648000,101.5921000,'Persiaran Tujuan, USJ 1',NULL,NULL,'GeoMap Ventures',NULL,'2026-09-10 20:31:05',NULL,NULL,NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(38,'RPT-WF-PENDING-ENG-01','MBSJ/ENG/JLN/2026/103',3,2,1,'Kebocoran paip bawah tanah SS3','Air bertakung menunjukkan kemungkinan kebocoran paip utiliti.','submitted','pending_engineer_verification',3.0821000,101.6108000,'Jalan SS3/45, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-08 20:31:05',NULL,NULL,NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(39,'RPT-WF-RETURNED-01','MBSJ/ENG/JLN/2026/104',4,2,1,'Retakan jalan SS7 — dikembalikan untuk pembetulan','Retakan panjang di permukaan jalan SS7. Foto lawatan belum lengkap.','submitted','engineer_returned',3.1073000,101.6067000,'Jalan SS7/13, Kelana Jaya',NULL,NULL,'GeoMap Ventures',NULL,'2026-09-06 20:31:05','Sila kemaskini foto keadaan sebenar dan lengkapkan ukuran kedalaman retakan.','2026-09-11 20:31:05',3,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(40,'RPT-WF-PENDING-DIR-01','MBSJ/ENG/JLN/2026/105',1,2,1,'Kemerosotan tanah berhampiran SS14 — menunggu kelulusan','Kemerosotan tanah kecil. Telah disahkan Engineer, menunggu Pengarah.','submitted','pending_director_approval',3.0912000,101.5987000,'Jalan SS14/1, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-04 20:31:05',NULL,'2026-09-12 01:10:00',3,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(41,'RPT-WF-APPROVED-01','MBSJ/ENG/JLN/2026/106',4,2,1,'Penyelenggaraan permukaan jalan USJ 9 — DILULUSKAN','Kerja penyelenggaraan permukaan jalan telah dilawat, disahkan dan diluluskan.','completed','approved',3.0489000,101.6012000,'Persiaran Kewajipan, USJ 9',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-08-29 20:31:05',NULL,'2026-09-07 20:31:05',3,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(42,'RPT-WF-REJECTED-01','MBSJ/ENG/JLN/2026/107',3,2,1,'Utiliti terdedah SS18 — ditolak Pengarah','Kabel utiliti terdedah. Ditolak kerana dokumentasi sokongan tidak mencukupi.','submitted','director_rejected',3.0551000,101.5789000,'Jalan SS18/1, Subang Jaya',NULL,NULL,'GeoMap Ventures',NULL,'2026-08-31 20:31:05',NULL,'2026-09-08 20:31:05',3,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(43,'RPT-WF-SALIRAN-01','MBSJ/ENG/SC/2026/201',6,20,8,'Sinkhole di SS12 — Saliran & Cerun','Longkang utama tersumbat menyebabkan banjir kilat. Data unit Saliran & Cerun sahaja.','submitted','pending_engineer_verification',3.0955000,101.6155000,'Jalan SS12/1, Subang Jaya',NULL,NULL,'DrainTech Survey',NULL,'2026-09-10 20:31:05',NULL,NULL,NULL,'2026-09-12 20:31:05','2026-09-12 21:07:46'),(44,'RPT-WF-CERUN-01','MBSJ/ENG/SC/2026/301',7,20,8,'Cerun Runtuh di Ara Damansara','Cerun tepi jalan menunjukkan tanda ketidakstabilan selepas hujan. Menunggu lawatan TA.','submitted','pending_site_visit',3.1289000,101.5784000,'Ara Damansara, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-12 10:31:06',NULL,NULL,NULL,'2026-09-12 20:31:06','2026-09-12 21:07:46'),(45,'RPT-WF-STR-01','MBSJ/ENG/STR/2026/401',4,2,3,'Retakan struktur jejambat USJ — Unit Structure','Retakan kecil pada struktur jejambat. Menunggu lawatan TA Structure.','submitted','pending_site_visit',3.0712000,101.5888000,'Jejambat USJ, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-11 20:31:06',NULL,NULL,NULL,'2026-09-12 20:31:06','2026-09-12 20:31:06'),(46,'RPT-WF-ME-01','MBSJ/ENG/ME/2026/501',3,2,9,'Lampu jalan rosak SS15 — Unit M&E','Beberapa tiang lampu jalan tidak berfungsi. Menunggu semakan TA M&E.','submitted','pending_site_visit',3.0741000,101.5861000,'Jalan SS15/2, Subang Jaya',NULL,NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-11 20:31:06',NULL,NULL,NULL,'2026-09-12 20:31:06','2026-09-12 20:31:06'),(47,'SC-2026-0001','MBSJ/ENG/SC/2026/001',6,20,8,'Sinkhole berhampiran longkang SS15','Lubang sinkhole muncul selepas hujan lebat. Perlu siasatan teknikal dan lawatan tapak.','submitted','pending_site_visit',3.0748000,101.5865000,'Jalan SS15/4, Subang Jaya','Jalan SS15/4, 47500 Subang Jaya',NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-10 21:07:46',NULL,NULL,NULL,'2026-09-12 20:57:56','2026-09-12 21:07:46'),(48,'SC-2026-0002',NULL,6,20,8,'Sinkhole kecil di kawasan parkir USJ','Draf laporan sinkhole untuk semakan lampiran teknikal.','draft',NULL,3.0622000,101.5911000,'USJ 1, Subang Jaya','Persiaran Tujuan, USJ 1',NULL,'ABC Survey Sdn Bhd',NULL,NULL,NULL,NULL,NULL,'2026-09-12 20:57:56','2026-09-12 20:57:56'),(49,'SC-2026-0003','MBSJ/ENG/SC/2026/003',6,20,8,'Kemerosotan permukaan jalan — disyaki sinkhole','Permukaan jalan merosot; menunggu lawatan TA.','submitted','pending_site_visit',3.0812000,101.5728000,'SS18, Subang Jaya','Jalan SS18/1',NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-12 03:07:46',NULL,NULL,NULL,'2026-09-12 20:57:56','2026-09-12 21:07:46'),(50,'CR-2026-0001','MBSJ/ENG/CR/2026/001',7,20,8,'Cerun Runtuh tepi jalan Ara Damansara','Cerun menunjukkan tanda ketidakstabilan selepas hujan berterusan.','submitted','pending_site_visit',3.1289000,101.5784000,'Ara Damansara, Subang Jaya','Persiaran Ara, Ara Damansara',NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-11 21:07:46',NULL,NULL,NULL,'2026-09-12 20:57:56','2026-09-12 21:07:46'),(51,'CR-2026-0002',NULL,7,20,8,'Retakan cerun di kawasan kediaman','Retakan pada cerun belakang rumah — draf surveyor.','draft',NULL,3.1105000,101.5602000,'Puchong Prima','Jalan Prima 3',NULL,'ABC Survey Sdn Bhd',NULL,NULL,NULL,NULL,NULL,'2026-09-12 20:57:56','2026-09-12 20:57:56'),(52,'CR-2026-0003','MBSJ/ENG/CR/2026/003',7,20,8,'Longsoran cerun kecil di Bukit Indah','Longsoran cetek selepas hujan. Menunggu lawatan tapak TA.','submitted','pending_site_visit',3.0455000,101.6055000,'Bukit Indah, Ampang','Jalan Indah 2/1',NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-12 13:07:46',NULL,NULL,NULL,'2026-09-12 21:07:46','2026-09-12 21:07:46'),(53,'CR-2026-0004','MBSJ/ENG/CR/2026/004',7,20,8,'Cerun Runtuh berhampiran saliran USJ 9','Cerun runtuh menjejaskan saliran tepi jalan. Perlu semakan teknikal.','submitted','site_visit_in_progress',3.0488000,101.5822000,'USJ 9, Subang Jaya','Persiaran Kewajipan, USJ 9',NULL,'ABC Survey Sdn Bhd',NULL,'2026-09-09 21:07:46',NULL,NULL,NULL,'2026-09-12 21:07:46','2026-09-12 21:07:46');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('ss7r4DYf8NudllUtv6zU5GJHxCbseyp3QfvlKotK',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJLSHlocHdWQlYxSzlCSUZWTXNkd1MyZVp5bzZYckhQWjlxd3lhUlpHIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3NhbGlyYW4tY2VydW4ifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9lbmdpbmVlcmluZyIsInJvdXRlIjoiZW5naW5lZXJpbmcuaHViIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9',1789304843);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_visit_photos`
--

DROP TABLE IF EXISTS `site_visit_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_visit_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_visit_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `taken_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_visit_photos_site_visit_id_foreign` (`site_visit_id`),
  KEY `site_visit_photos_user_id_foreign` (`user_id`),
  CONSTRAINT `site_visit_photos_site_visit_id_foreign` FOREIGN KEY (`site_visit_id`) REFERENCES `site_visits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `site_visit_photos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_visit_photos`
--

LOCK TABLES `site_visit_photos` WRITE;
/*!40000 ALTER TABLE `site_visit_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_visit_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_visits`
--

DROP TABLE IF EXISTS `site_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_visits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint unsigned NOT NULL,
  `ta_user_id` bigint unsigned NOT NULL,
  `file_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `gps_accuracy` decimal(8,2) DEFAULT NULL,
  `laporan_pj_pjk` longtext COLLATE utf8mb4_unicode_ci,
  `visit_notes` text COLLATE utf8mb4_unicode_ci,
  `ta_designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ta_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','submitted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_visits_report_id_unique` (`report_id`),
  KEY `site_visits_ta_user_id_foreign` (`ta_user_id`),
  CONSTRAINT `site_visits_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `site_visits_ta_user_id_foreign` FOREIGN KEY (`ta_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_visits`
--

LOCK TABLES `site_visits` WRITE;
/*!40000 ALTER TABLE `site_visits` DISABLE KEYS */;
INSERT INTO `site_visits` VALUES (16,34,18,'MBSJ/ENG/INF/2026/701','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-05','09:00:00',3.0522000,101.5822000,6.00,'Pemerhatian lapangan unit Infrastruktur.',NULL,'Pembantu Teknik','Diana TA (Infrastruktur)','submitted','2026-09-05 16:31:35','2026-09-05 22:31:35','2026-09-05 22:31:35'),(20,3,4,'','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-10','07:23:00',NULL,NULL,NULL,'penemuan cerun bahaya',NULL,'Pembantu Teknik','Ali TA (Jalan)','submitted','2026-09-09 23:25:46','2026-09-09 23:23:52','2026-09-09 23:25:46'),(21,6,4,'','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-10','07:26:00',NULL,NULL,NULL,'',NULL,'Pembantu Teknik','Ali TA (Jalan)','draft',NULL,'2026-09-09 23:26:50','2026-09-09 23:26:57'),(22,1,4,NULL,'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-10','07:32:57',NULL,NULL,NULL,NULL,NULL,'Pembantu Teknik',NULL,'draft',NULL,'2026-09-09 23:32:57','2026-09-09 23:32:57'),(23,4,4,'','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-10','07:55:00',NULL,NULL,NULL,'cerun bahaya perlu perhatian dan pembaikan segera\ncerun bahaya perlu perhatian dan pembaikan segera\ncerun bahaya perlu perhatian dan pembaikan segera','test','Pembantu Teknik','Ali TA (Jalan)','submitted','2026-09-09 23:57:45','2026-09-09 23:55:24','2026-09-09 23:57:45'),(24,37,4,'MBSJ/ENG/JLN/2026/102','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-13','10:30:00',3.0649000,101.5923000,8.50,'Pemerhatian awal:\n- Lubang disahkan wujud di lorong kiri.\n- Laluan masih boleh dilalui dengan berhati-hati.\n- Menunggu foto tambahan dan ukuran mendalam.',NULL,'Pembantu Teknik','Ali TA (Jalan)','draft',NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(25,38,4,'MBSJ/ENG/JLN/2026/103','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-10','10:15:00',3.0823000,101.6110000,6.20,'LAPORAN PJ/PJK\n\n1. Keadaan sebenar: Air bertakung di bahu jalan SS3/45.\n2. Punca digambarkan berkaitan utiliti bawah tanah.\n3. Cadangan: Semakan bersama unit utiliti & tutup sementara kawasan.\n4. Risiko: Gelinciran kenderaan semasa hujan.','Keadaan jalan basah; ambil gambar keseluruhan & close-up.','Pembantu Teknik','Ali TA (Jalan)','submitted','2026-09-10 03:20:00','2026-09-12 20:31:05','2026-09-12 20:31:05'),(26,39,4,'MBSJ/ENG/JLN/2026/104','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-11','14:00:00',3.1074000,101.6068000,10.00,'Retakan sepanjang kira-kira 15m dikesan. Foto close-up belum lengkap — perlu semakan semula.',NULL,'Pembantu Teknik','Ali TA (Jalan)','draft',NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(27,40,4,'MBSJ/ENG/JLN/2026/105','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-10','09:45:00',3.0913000,101.5989000,5.50,'Pemerhatian:\n- Kemerosotan tanah kecil (~0.4m) berhampiran bahu jalan.\n- Tiada ancaman segera kepada struktur bangunan.\n- Cadangan pemantauan berkala & penutupan sementara.',NULL,'Pembantu Teknik','Ali TA (Jalan)','submitted','2026-09-09 20:31:05','2026-09-12 20:31:05','2026-09-12 20:31:05'),(28,41,4,'MBSJ/ENG/JLN/2026/106','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-03','11:00:00',3.0490000,101.6014000,4.80,'Kerja penyelenggaraan telah dilaksanakan mengikut spesifikasi.\nPermukaan jalan dalam keadaan memuaskan selepas kerja tampalan.',NULL,'Pembantu Teknik','Ali TA (Jalan)','submitted','2026-09-02 20:31:05','2026-09-12 20:31:05','2026-09-12 20:31:05'),(29,42,4,'MBSJ/ENG/JLN/2026/107','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-06','15:20:00',3.0552000,101.5790000,7.10,'Kabel utiliti terdedah di tepi jalan. Pelan tapak masih ringkas.',NULL,'Pembantu Teknik','Ali TA (Jalan)','submitted','2026-09-05 20:31:05','2026-09-12 20:31:05','2026-09-12 20:31:05'),(30,43,21,'MBSJ/ENG/SC/2026/201','MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)','2026-09-12','08:40:00',3.0956000,101.6156000,5.00,'Longkang tersumbat dengan sampah & mendapan. Perlu pembersihan segera.',NULL,'Pembantu Teknik','Fatimah TA (Saliran & Cerun)','submitted','2026-09-11 20:31:06','2026-09-12 20:31:06','2026-09-12 20:31:06');
/*!40000 ALTER TABLE `site_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint unsigned DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_code_unique` (`code`),
  KEY `units_parent_id_foreign` (`parent_id`),
  CONSTRAINT `units_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'Jalan','JLN','Unit Jalan — Engineering',NULL,'active',2,'2026-09-05 04:42:23','2026-09-12 20:13:09'),(3,'Structure','STR','Unit Structure — Engineering',NULL,'active',3,'2026-09-05 04:42:23','2026-09-12 20:13:09'),(6,'Infrastruktur','INF','Unit Infrastruktur — Engineering',NULL,'inactive',6,'2026-09-05 04:42:23','2026-09-12 21:07:46'),(8,'Saliran & Cerun','SAL-CERUN','Unit Saliran & Cerun — Sinkhole dan Cerun Runtuh',NULL,'active',1,'2026-09-12 20:13:09','2026-09-12 20:13:09'),(9,'M&E','ME','Unit M&E (Mekanikal & Elektrik) — Engineering',NULL,'active',4,'2026-09-12 20:13:09','2026-09-12 20:13:09');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superadmin','surveyor','engineer','ta','director') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'engineer',
  `unit_id` bigint unsigned DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_unit_id_foreign` (`unit_id`),
  CONSTRAINT `users_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin','admin@mbsj.gov.my','superadmin',NULL,'03-8000-0001','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy','isqoL66LQO3NdOoFdWGBXOiSpH2e9Xo6t6KUef0sDOIytupnwaA6BwpN9lym','2026-09-03 04:57:34','2026-09-12 20:31:05'),(2,'Ahmad Surveyor (Vendor)','surveyor@mbsj.gov.my','surveyor',1,'012-1111001','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy',NULL,'2026-09-03 04:57:34','2026-09-12 20:31:05'),(3,'Rahman Engineer (Jalan)','engineer@mbsj.gov.my','engineer',1,'012-3333001','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy',NULL,'2026-09-03 04:57:35','2026-09-12 20:31:05'),(4,'Ali TA (Jalan)','ta@mbsj.gov.my','ta',1,'012-2222001','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy','URKEDqtkJSNFFp7C2jlrD3SD1irP8G0V4jA1JwjmcrtewMJpXh2zUeWaHJFy','2026-09-05 05:09:00','2026-09-12 20:31:05'),(5,'Pengarah Kejuruteraan','director@mbsj.gov.my','director',NULL,'03-8000-0099','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy',NULL,'2026-09-05 05:09:01','2026-09-12 20:31:05'),(6,'Siti Surveyor (Saliran)','surveyor.saliran@mbsj.gov.my','surveyor',8,'012-1111002','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 05:25:32','2026-09-12 20:32:09'),(7,'Fatimah TA (Saliran)','ta.saliran@mbsj.gov.my','ta',8,'012-2222002','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 05:25:33','2026-09-12 20:32:09'),(8,'Kumar Engineer (Saliran)','engineer.saliran@mbsj.gov.my','engineer',8,'012-3333002','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 05:25:33','2026-09-12 20:32:09'),(9,'Lim Engineer (Cerun)','engineer.cerun@mbsj.gov.my','engineer',8,'012-3333007','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 05:25:33','2026-09-12 20:32:09'),(10,'Hassan Surveyor (Cerun)','surveyor.cerun@mbsj.gov.my','surveyor',8,NULL,'inactive',NULL,'$2y$12$RI2R7EHV7XKOQrguWIfVh.kZ/e9.lfvPUqgGa0fXrTzYlDqIPj3j.',NULL,'2026-09-05 05:25:33','2026-09-12 20:32:09'),(11,'Nora TA (Cerun)','ta.cerun@mbsj.gov.my','ta',8,'012-2222007','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a','7AUO2FrGumBoxJykcchgKfk6ml221ClMnKT72FwHQvCd8EHB4QfNs6ycG9Fb','2026-09-05 05:25:33','2026-09-12 20:32:09'),(12,'Wong TA (Struktur)','ta.struktur@mbsj.gov.my','ta',3,'012-2222003','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a','f40jWj66MHKdhuVX6xOeOV1sm74ch4SR8haH7PjW8SnldUOTCzSkNpwPwZaZ','2026-09-05 22:31:35','2026-09-12 20:32:09'),(13,'Azlan Engineer (Struktur)','engineer.struktur@mbsj.gov.my','engineer',3,'012-3333003','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 22:31:35','2026-09-12 20:32:09'),(14,'Ravi TA (Elektrik)','ta.elektrik@mbsj.gov.my','ta',9,'012-2222004','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 22:31:35','2026-09-12 20:32:09'),(15,'Mei Engineer (Elektrik)','engineer.elektrik@mbsj.gov.my','engineer',9,'012-3333004','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 22:31:35','2026-09-12 20:32:09'),(16,'Hassan TA (Mekanikal)','ta.mekanikal@mbsj.gov.my','ta',9,'012-2222005','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 22:31:35','2026-09-12 20:32:09'),(17,'Priya Engineer (Mekanikal)','engineer.mekanikal@mbsj.gov.my','engineer',9,'012-3333005','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 22:31:35','2026-09-12 20:32:09'),(18,'Diana TA (Infrastruktur)','ta.infrastruktur@mbsj.gov.my','ta',6,'012-2222006','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 22:31:35','2026-09-12 20:32:09'),(19,'Farid Engineer (Infrastruktur)','engineer.infrastruktur@mbsj.gov.my','engineer',6,'012-3333006','inactive',NULL,'$2y$12$pGjP4sikzkkHMc/Fqt9IEOwbYWEBd6sJ.X7GNoXBVkimKD5ryvq0a',NULL,'2026-09-05 22:31:35','2026-09-12 20:32:09'),(20,'Surveyor Saliran & Cerun','surveyor.saliran-cerun@mbsj.gov.my','surveyor',8,'012-7001003','active',NULL,'$2y$12$G25UX6W.87ItHmr5M9XZ8.iyt14nVjdaPJKwlNei4BTgsInR4hllu',NULL,'2026-09-12 20:31:05','2026-09-12 21:07:46'),(21,'TA Saliran & Cerun','ta.saliran-cerun@mbsj.gov.my','ta',8,'012-7001001','active',NULL,'$2y$12$G25UX6W.87ItHmr5M9XZ8.iyt14nVjdaPJKwlNei4BTgsInR4hllu',NULL,'2026-09-12 20:31:05','2026-09-12 21:07:46'),(22,'Engineer Saliran & Cerun','engineer.saliran-cerun@mbsj.gov.my','engineer',8,'012-7001002','active',NULL,'$2y$12$G25UX6W.87ItHmr5M9XZ8.iyt14nVjdaPJKwlNei4BTgsInR4hllu',NULL,'2026-09-12 20:31:05','2026-09-12 21:07:46'),(23,'Wong TA (Structure)','ta.structure@mbsj.gov.my','ta',3,'012-2222003','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy',NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(24,'Azlan Engineer (Structure)','engineer.structure@mbsj.gov.my','engineer',3,'012-3333003','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy',NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(25,'Ravi TA (M&E)','ta.me@mbsj.gov.my','ta',9,'012-2222004','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy',NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05'),(26,'Mei Engineer (M&E)','engineer.me@mbsj.gov.my','engineer',9,'012-3333004','active',NULL,'$2y$12$mxhHdvyS4LyS0IVec0rvrukJK1IVSnHMVuJe5gC4k7K9E1XL8ikYy',NULL,'2026-09-12 20:31:05','2026-09-12 20:31:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_histories`
--

DROP TABLE IF EXISTS `workflow_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `workflow_histories_report_id_foreign` (`report_id`),
  KEY `workflow_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `workflow_histories_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workflow_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_histories`
--

LOCK TABLES `workflow_histories` WRITE;
/*!40000 ALTER TABLE `workflow_histories` DISABLE KEYS */;
INSERT INTO `workflow_histories` VALUES (49,32,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-04 22:31:35'),(50,34,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-04 22:31:35'),(51,34,18,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Lawatan dihantar','127.0.0.1','2026-09-05 16:31:35'),(55,3,4,'ta','start_site_visit','pending_site_visit','site_visit_in_progress','Lawatan tapak dimulakan','127.0.0.1','2026-09-09 23:23:52'),(56,3,4,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Laporan lawatan tapak dihantar','127.0.0.1','2026-09-09 23:25:46'),(57,6,4,'ta','start_site_visit','pending_site_visit','site_visit_in_progress','Lawatan tapak dimulakan','127.0.0.1','2026-09-09 23:26:50'),(58,1,4,'ta','start_site_visit','pending_site_visit','site_visit_in_progress','Lawatan tapak dimulakan','127.0.0.1','2026-09-09 23:32:57'),(59,4,4,'ta','start_site_visit','pending_site_visit','site_visit_in_progress','Lawatan tapak dimulakan','127.0.0.1','2026-09-09 23:55:24'),(60,4,4,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Laporan lawatan tapak dihantar','127.0.0.1','2026-09-09 23:57:45'),(61,36,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar oleh Surveyor','127.0.0.1','2026-09-11 20:31:05'),(62,37,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-10 20:31:05'),(63,37,4,'ta','start_site_visit','pending_site_visit','site_visit_in_progress','Lawatan tapak dimulakan','127.0.0.1','2026-09-12 15:31:05'),(64,38,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-08 20:31:05'),(65,38,4,'ta','start_site_visit','pending_site_visit','site_visit_in_progress','Lawatan dimulakan','127.0.0.1','2026-09-10 01:00:00'),(66,38,4,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Laporan lawatan dihantar','127.0.0.1','2026-09-10 03:20:00'),(67,39,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-06 20:31:05'),(68,39,4,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Lawatan dihantar','127.0.0.1','2026-09-10 20:31:05'),(69,39,3,'engineer','engineer_return','pending_engineer_verification','engineer_returned','Sila kemaskini foto keadaan sebenar dan lengkapkan ukuran kedalaman retakan.','127.0.0.1','2026-09-11 20:31:05'),(70,40,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-04 20:31:05'),(71,40,4,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Lawatan dihantar','127.0.0.1','2026-09-09 20:31:05'),(72,40,3,'engineer','engineer_verify','pending_engineer_verification','pending_director_approval','Kerja lapangan dan lawatan adalah konsisten. Disyorkan untuk diluluskan.','127.0.0.1','2026-09-12 01:10:00'),(73,41,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-08-29 20:31:05'),(74,41,4,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Lawatan dihantar','127.0.0.1','2026-09-02 20:31:05'),(75,41,3,'engineer','engineer_verify','pending_engineer_verification','pending_director_approval','Disahkan lengkap.','127.0.0.1','2026-09-07 20:31:05'),(76,41,5,'director','director_approve','pending_director_approval','approved','Diluluskan untuk rekod rasmi.','127.0.0.1','2026-09-09 06:30:00'),(77,42,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-08-31 20:31:05'),(78,42,4,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Lawatan dihantar','127.0.0.1','2026-09-05 20:31:05'),(79,42,3,'engineer','engineer_verify','pending_engineer_verification','pending_director_approval','Disahkan untuk semakan Pengarah.','127.0.0.1','2026-09-08 20:31:05'),(80,42,5,'director','director_reject','pending_director_approval','director_rejected','Dokumen sokongan dan pelan tapak tidak mencukupi. Sila lengkapkan.','127.0.0.1','2026-09-09 20:31:05'),(81,43,20,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-10 20:31:06'),(82,43,21,'ta','submit_site_visit','site_visit_in_progress','pending_engineer_verification','Lawatan Saliran & Cerun dihantar','127.0.0.1','2026-09-11 20:31:06'),(83,44,20,'surveyor','submit_report',NULL,'pending_site_visit','Laporan Cerun dihantar','127.0.0.1','2026-09-12 10:31:06'),(84,45,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-11 20:31:06'),(85,46,2,'surveyor','submit_report',NULL,'pending_site_visit','Laporan dihantar','127.0.0.1','2026-09-11 20:31:06'),(86,38,1,'superadmin','pdf_generated','pending_engineer_verification','pending_engineer_verification','Site Visit PDF digenerate','127.0.0.1','2026-09-13 04:45:56'),(87,47,20,'surveyor','submit_report',NULL,'pending_site_visit','Demo case dihantar','127.0.0.1','2026-09-13 04:57:56'),(88,49,20,'surveyor','submit_report',NULL,'pending_site_visit','Demo case dihantar','127.0.0.1','2026-09-13 04:57:56'),(89,50,20,'surveyor','submit_report',NULL,'pending_site_visit','Demo case dihantar','127.0.0.1','2026-09-13 04:57:56'),(90,52,20,'surveyor','submit_report',NULL,'pending_site_visit','Demo case dihantar','127.0.0.1','2026-09-13 05:07:46'),(91,7,1,'superadmin','pdf_generated',NULL,NULL,'Site Visit PDF digenerate','127.0.0.1','2026-09-13 05:12:13');
/*!40000 ALTER TABLE `workflow_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'gis_mbpj'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-13 21:12:48
