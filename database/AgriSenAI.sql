-- Schema AgriSenAi v1 (ho so lo xoai) — sinh bang mysqldump --no-data tu migrations ngay 22/10/2026.
-- Nguon su that la database/migrations; file nay chi de doc/nop kem thuyet minh.


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
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nhan_vien / admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'dang nhap bang SDT',
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'diem thu gom / HTX',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'vd XL-261022-AB12, in tren tem QR',
  `supplier` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nha vuon / nguon cung',
  `variety` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'giong, nguoi nhap',
  `purpose` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'an_xanh / an_chin',
  `weight_kg` decimal(10,2) NOT NULL,
  `harvest_date` date NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft / analyzing / analyzed / confirmed / published',
  `created_by` bigint unsigned NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lots_code_unique` (`code`),
  KEY `lots_created_by_foreign` (`created_by`),
  CONSTRAINT `lots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lot_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lot_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'anh DA chuan hoa 768x768, disk public',
  `sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `view` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'toan_qua / be_mat / cuong / khac',
  `quality` json NOT NULL COMMENT '{ok, issues[], blur, brightness, width, height}',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lot_photos_lot_id_foreign` (`lot_id`),
  CONSTRAINT `lot_photos_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ai_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_suggestions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` bigint unsigned NOT NULL,
  `model` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_ids` json NOT NULL,
  `result` json DEFAULT NULL COMMENT 'NULL neu loi',
  `error` text COLLATE utf8mb4_unicode_ci,
  `latency_ms` int unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ai_suggestions_lot_id_foreign` (`lot_id`),
  CONSTRAINT `ai_suggestions_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `verified_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `verified_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` bigint unsigned NOT NULL,
  `suggestion_id` bigint unsigned DEFAULT NULL,
  `criterion` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'color / surface / shape / uniformity / ripeness',
  `decision` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'accept / edit / reject / manual',
  `ai_value` json DEFAULT NULL,
  `final_value` json DEFAULT NULL COMMENT 'NULL khi reject',
  `note` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmed_by` bigint unsigned NOT NULL,
  `confirmed_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `verified_labels_lot_id_foreign` (`lot_id`),
  KEY `verified_labels_suggestion_id_foreign` (`suggestion_id`),
  KEY `verified_labels_confirmed_by_foreign` (`confirmed_by`),
  CONSTRAINT `verified_labels_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `verified_labels_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `verified_labels_suggestion_id_foreign` FOREIGN KEY (`suggestion_id`) REFERENCES `ai_suggestions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lot_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lot_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` bigint unsigned NOT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'created / photo_added / ai_suggested / confirmed / published',
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON sort key, dung chuoi da bam',
  `hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lot_audit_logs_lot_id_foreign` (`lot_id`),
  KEY `lot_audit_logs_actor_id_foreign` (`actor_id`),
  CONSTRAINT `lot_audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `lot_audit_logs_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

