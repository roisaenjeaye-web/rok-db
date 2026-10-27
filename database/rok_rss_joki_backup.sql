-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: rok_rss_joki
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
INSERT INTO `cache` VALUES ('laravel-cache-5722bf7ce7fb66ecc9564b9f522aa467','i:1;',1793113825),('laravel-cache-5722bf7ce7fb66ecc9564b9f522aa467:timer','i:1793113825;',1793113825);
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
-- Table structure for table `characters`
--

DROP TABLE IF EXISTS `characters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `characters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `game_account_id` bigint unsigned NOT NULL,
  `kd_number` int NOT NULL,
  `ign` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','banned','zeroed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_farmed_at` timestamp NULL DEFAULT NULL,
  `farm_count` int NOT NULL DEFAULT '0',
  `farmer_stats` json DEFAULT NULL,
  `last_farmed_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `characters_game_account_id_foreign` (`game_account_id`),
  KEY `characters_kd_number_index` (`kd_number`),
  KEY `characters_last_farmed_at_index` (`last_farmed_at`),
  KEY `characters_kd_number_last_farmed_at_index` (`kd_number`,`last_farmed_at`),
  CONSTRAINT `characters_game_account_id_foreign` FOREIGN KEY (`game_account_id`) REFERENCES `game_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `characters`
--

LOCK TABLES `characters` WRITE;
/*!40000 ALTER TABLE `characters` DISABLE KEYS */;
INSERT INTO `characters` VALUES (2,2,2359,'Farm KD 2359','aktif','2026-10-27 03:35:41','2026-10-27 09:08:40','2026-10-27 09:08:40',4,'{\"Rois\": 4}','Rois'),(3,2,2359,'Farm KD 2359','aktif','2026-10-27 03:35:41','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(4,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(5,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(6,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(7,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(8,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(9,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(10,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(11,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(12,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(13,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(14,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(15,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(16,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(17,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(18,2,2359,'Farm KD 2359','aktif','2026-10-27 07:11:34','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(19,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(20,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(21,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(22,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(23,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(24,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(25,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(26,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(27,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(28,3,2359,'Farm KD 2359','aktif','2026-10-27 07:12:39','2026-10-27 09:08:40','2026-10-27 09:08:40',3,'{\"Rois\": 3}','Rois'),(29,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:35','2026-10-27 07:24:35',1,NULL,NULL),(30,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:36','2026-10-27 07:24:36',1,NULL,NULL),(31,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:36','2026-10-27 07:24:36',1,NULL,NULL),(32,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:36','2026-10-27 07:24:36',1,NULL,NULL),(33,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:36','2026-10-27 07:24:36',1,NULL,NULL),(34,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:36','2026-10-27 07:24:36',1,NULL,NULL),(35,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:36','2026-10-27 07:24:36',1,NULL,NULL),(36,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(37,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(38,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(39,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(40,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(41,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(42,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(43,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:37','2026-10-27 07:24:37',1,NULL,NULL),(44,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:38','2026-10-27 07:24:38',1,NULL,NULL),(45,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:38','2026-10-27 07:24:38',1,NULL,NULL),(46,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:38','2026-10-27 07:24:38',1,NULL,NULL),(47,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:38','2026-10-27 07:24:38',1,NULL,NULL),(48,2,3868,'Farm KD 3868','aktif','2026-10-27 07:18:49','2026-10-27 07:24:38','2026-10-27 07:24:38',1,NULL,NULL);
/*!40000 ALTER TABLE `characters` ENABLE KEYS */;
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
-- Table structure for table `game_accounts`
--

DROP TABLE IF EXISTS `game_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `game_accounts_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_accounts`
--

LOCK TABLES `game_accounts` WRITE;
/*!40000 ALTER TABLE `game_accounts` DISABLE KEYS */;
INSERT INTO `game_accounts` VALUES (2,'roisalif20@gmail.com','roisalif20','lilith','2026-10-27 03:35:20','2026-10-27 03:35:20'),(3,'roisalif13@gmail.com','rois2018',NULL,'2026-10-27 07:12:19','2026-10-27 07:12:19');
/*!40000 ALTER TABLE `game_accounts` ENABLE KEYS */;
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
-- Table structure for table `kd_farmer_stats`
--

DROP TABLE IF EXISTS `kd_farmer_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kd_farmer_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kd_number` int NOT NULL,
  `worker_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `farm_count` int NOT NULL DEFAULT '0',
  `last_farmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kd_farmer_stats_kd_number_worker_name_unique` (`kd_number`,`worker_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kd_farmer_stats`
--

LOCK TABLES `kd_farmer_stats` WRITE;
/*!40000 ALTER TABLE `kd_farmer_stats` DISABLE KEYS */;
INSERT INTO `kd_farmer_stats` VALUES (1,2359,'Rois',102,'2026-10-27 09:08:40','2026-10-27 07:47:38','2026-10-27 09:08:40'),(2,2359,'Therta',20,NULL,'2026-10-27 07:47:38','2026-10-27 07:48:11');
/*!40000 ALTER TABLE `kd_farmer_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kingdoms`
--

DROP TABLE IF EXISTS `kingdoms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kingdoms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kd_number` int NOT NULL,
  `food` decimal(10,2) NOT NULL DEFAULT '0.00',
  `wood` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stone` decimal(10,2) NOT NULL DEFAULT '0.00',
  `gold` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kingdoms_kd_number_unique` (`kd_number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kingdoms`
--

LOCK TABLES `kingdoms` WRITE;
/*!40000 ALTER TABLE `kingdoms` DISABLE KEYS */;
INSERT INTO `kingdoms` VALUES (2,2359,80.00,40.00,0.00,0.00,'2026-10-27 08:16:51','2026-10-27 08:35:14'),(4,3868,0.00,0.00,0.00,0.00,'2026-10-27 08:50:29','2026-10-27 08:50:29');
/*!40000 ALTER TABLE `kingdoms` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_27_074010_create_kingdoms_table',1),(5,'2026_10_27_074011_create_orders_table',1),(9,'2026_10_27_100612_create_game_accounts_table',2),(10,'2026_10_27_100613_create_characters_table',2),(11,'2026_10_27_102651_add_farm_tracking_to_characters',2),(12,'2026_10_27_110000_add_worker_tracking_and_kd_farmer_stats',3),(13,'2026_10_27_120000_create_salaries_table',4),(14,'2026_10_27_160216_add_indexes_to_characters_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `buyer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kd_number` int NOT NULL,
  `rss_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_requested` decimal(10,2) NOT NULL,
  `amount_to_send` decimal(10,2) NOT NULL,
  `tax_percentage` int NOT NULL DEFAULT '8',
  `total_price` decimal(15,2) NOT NULL,
  `status` enum('pending','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
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
-- Table structure for table `salaries`
--

DROP TABLE IF EXISTS `salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salaries`
--

LOCK TABLES `salaries` WRITE;
/*!40000 ALTER TABLE `salaries` DISABLE KEYS */;
/*!40000 ALTER TABLE `salaries` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('5qhd0lbB91cwNKBPZLS0Hu7WzhQTk02kQM47wNIz',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiaUF1MVdzZXJkcDQ0VVZRazNFRktFZDdRaTVUeE1LTWlxZTZNSzVPZCIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2thbGt1bGF0b3IiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793116829),('7DV1UjYhH4Sbz2ggHghglFJOr5xBYYG4RZtJxKWL',NULL,'127.0.0.1','Symfony','eyJfdG9rZW4iOiJiUEJTTjcyNmJOb2tUeEZNVEN3dXNaTkgyU3hqTVRLdng5N3MxajROIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793113575),('81OONbDbliHn0iMSYwLaVf2jurlv0xZKsGvb1G7k',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiaGt0NzFEZTFzcnJHS2JTMWVKbmx2ZkJmdUdyNnlIdTZFWm40elBJTSIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2FrdW4iLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793114658),('8EFoBKOqtARaIQkmxqtworDws599dOQ5VWLmuN1L',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJ0QUM2eWNXRFVQc2hnanNzY0wwc0NPcm1lR1hVUFMyM1pZREhRNzByIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9ha3VuIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1793111794),('aRR3M3O4UIkHaLLEZtH4Nq7b7oY3N24vYpT3PedG',1,'127.0.0.1','Symfony','eyJfdG9rZW4iOiJnaGdLSXJGakhJOFRuQXFWSUw4Um81S041WW0zc1I4NGxpMklQRlVjIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793115298),('avkhH7uq1N96i06iYOUl5yyADiEq9jz9fNioWiis',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiQk03WFlsa1Q4WWp0Q1NGZDRjNmdmenJ0Uk5neGM5WFRaamdVQUMzciIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsInN1Y2Nlc3MiOiJEYXRhIGdhamkgUm9pcyBiZXJoYXNpbCBkaXBlcmJhcnVpISIsIl9mbGFzaCI6eyJuZXciOltdLCJvbGQiOlsic3VjY2VzcyIsInN1Y2Nlc3MiXX0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL3N0b2siLCJyb3V0ZSI6ImhvbWUifX0=',1793116313),('BtLOFZeyCvib4JOTgkN0CxPO2tlz9FVxuRDjxYrf',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoieUMyUXNRb0xIR2Z1RnhDM0tDRDVxYk1iUUYwaWpyc0h2N21melZaNSIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2FrdW4iLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793114262),('CvVENuRjT2A3sPz086ARm6tINxEK8T8JEjcIGs8L',NULL,'127.0.0.1','Symfony','eyJfdG9rZW4iOiJoeDRKRkhzTm5wNHlpQjExMTllWjJFQUVlRGZCV0NBN3gyc1pNZjV3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793113055),('Dar9BAeK5xinyj1Y13zrydjOeKShtSHdWQQf7D2B',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiUlZ1YWhNSG00TlFWMzFVVVlWOGVNRHIxQUZ1WVZpNnh4NnV1SmdJZyIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsInN1Y2Nlc3MiOiJEYXRhIGdhamkgUm9pcyBiZXJoYXNpbCBkaXBlcmJhcnVpISIsIl9mbGFzaCI6eyJuZXciOltdLCJvbGQiOlsic3VjY2VzcyIsInN1Y2Nlc3MiXX0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL3N0b2siLCJyb3V0ZSI6ImhvbWUifX0=',1793116267),('hENeHUSU29iRDabE0cEVAwmmoNgtWkDHzq4j7jqC',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9711','eyJfdG9rZW4iOiIwak5rWlJQMXNCUmNIWjdUSXpDN2t1aDZFVTd3cWNTaVpUNG5MSU1lIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWwiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793111730),('hy8u0GWikzsL1BO854t7lExFplL8M5vmfwI8kbAb',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoibU54eDRWaTJZTm1saXRkcTZtTkhBbWhqYkx3QjNMT3lhNHp1Z3JVRCIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2thbGt1bGF0b3IiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793117301),('HzoQ6EEQn6m2NVogmnWFgWIw7P9G1PFhtkZ7BwwN',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoicTAzenV6bzZhdzB0Rm9QZWRUaU1vRDFVUWRPVzllWDQ2VW1nMGl1aCIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793117094),('IB1jWjYOrVbR5MPfkwX21zSjHXo0cLFja1ufg11z',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJvdDVIN2ZvdnVtRjZkd1RZcVdOMHZOZzNBRmIxUmM0c2lZSnJ3NzNaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWwiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793111786),('JpNG6XkGPLCEJb8aOO1B1aMKeOsOCPmqmEWos4jb',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiUjNpajBIeFVvTEpDYm11eE9XdTNJbHBxV25iSmJkZGh3Z2s0TmVhMCIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793117083),('kjiU2eKd2uHu0eFDKhXmY8mp8TIrICA1ElttaLBK',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJiNlNxT1RLOHRTc01IMzdwc29RY1NIeVl3R2ZWNU92S2c4eE02UTJ2IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1793112425),('luhWOPIeChDj1tRYrjIBHLkLwkayhhhVDrklp9lO',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoicVE1bmJBMjdjaE1MaXY3MWlWR0JPNXZ1NnZqemhSUmQwYXFLMXBtQiIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsImZhcm1fcmF0ZSI6MTAwMCwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvc3RvayIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1793115825),('nfCkyu7P00UMaPjCpzK5o0ZJIZdTN9ub4522Tg78',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiT1JIZmRrTkVzTlFyVlFnT2VZemZrcmdpTzNtQldyamMwZTVYS1FBUyIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2thbGt1bGF0b3IiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793117294),('PRunmb9vcszONyiDBZwdOx2JPLGcdNrGTUaFpKmD',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0','eyJfdG9rZW4iOiJlSDdqYVl4c2pJYkJaSnpoN1dzY2dqV3hpUlowcFRXQUtQallCU0VaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWwiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwiYWN0aXZlX3dvcmtlciI6IlRoZXJ0YSIsImN1c3RvbV93b3JrZXJzIjpbIlRoZXJ0YSJdLCJ1cmwiOltdLCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF93ZWIiOiIyNGEwMjA2ZDllNWExNGIxYWY0ZWM4MDYyM2Y5Y2FlMmQyZDIzN2FkMWE4Y2Y1MzYyNTJmODVlNTQ1ODg1OTliIiwiZmFybV9yYXRlIjoxMDAwfQ==',1793117418),('qbuwfumh4RLAyMEMWZ1ai5nWkftEqQYlUG81Izvt',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiNUhqN0h4dFZzSld1SU9wcXd5UnZuaEE0Tlp0cExkajJjNjVHZzI1ZCIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2phZHdhbCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1793113656),('sN9rlAYXSuHsFRM7KjBGA32hGwpKegDaL6AfTS0y',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiI3b0tCNlFrRWt2TVdYYTRpcXhoU2JZM2RMdThQMGNlVzlXZFlDUGc4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWwiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793112408),('TbhdZ5wk8rXwnSSXLxIpqX0Bfm1QydTQbi1r4DTR',1,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoieDZpUWtrUFJjY3Z2aGI1b25wYVFzUWZYdnh4WVFRS2ZmeFpDWEFPZiIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjRhMDIwNmQ5ZTVhMTRiMWFmNGVjODA2MjNmOWNhZTJkMmQyMzdhZDFhOGNmNTM2MjUyZjg1ZTU0NTg4NTk5YiIsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793115277),('tnnTtwhuTA64pLjITTAosHWfWzY9q1xl4m7nYhjK',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJERDR3WVJVQUp5Zzh4VGhmUGRIc1dJbkcwenZoTzBsSWc1V3lQWnpNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9ha3VuIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1793112413),('xNtBmojX0Um90jX97754lWr7OPum2DjF8GOqhGGP',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiIyQlJrbXdPYVNTR1Y3S0ZhTGxwc2VVOW9ZT1RqY1owWkFUV2pOaXhkIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWwiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1793112500),('yJGGy9TONhuJRf1xM8eKWBTGu9P7fHl0sCMKPb0z',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJWdkZSMmY4OWxvR1dFV3JGT21pNzFxRUpnMGd1eXFOSmdVZDBqVTFkIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1793113083);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
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
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Rois','roisalif20@gmail.com',NULL,'$2y$12$DNZRmBEx1ppFYFC/K6WCWOxORDvbgotahDpTplsEyx5k1fiyj4/z6','akORFTknGKhhZBlS87Swm4g3lP3JQ8HwggqOrjiFfEHAVuH0lTdH9XgOVOIU','2026-10-27 02:45:08','2026-10-27 08:01:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-27 23:16:15
