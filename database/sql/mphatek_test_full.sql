CREATE DATABASE IF NOT EXISTS `mphatek_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mphatek_test`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fd_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fd_rates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `min_days` int(10) unsigned NOT NULL,
  `max_days` int(10) unsigned NOT NULL,
  `general_rate` decimal(5,2) NOT NULL,
  `senior_rate` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned DEFAULT NULL,
  `product` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'direct',
  `medium` varchar(255) DEFAULT NULL,
  `campaign` varchar(255) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `landing_page` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_page_id_foreign` (`page_id`),
  CONSTRAINT `leads_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `noindex` tinyint(1) NOT NULL DEFAULT 0,
  `canonical_url` varchar(255) DEFAULT NULL,
  `redirect_url` varchar(255) DEFAULT NULL,
  `redirect_code` smallint(5) unsigned NOT NULL DEFAULT 301,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_type_slug_unique` (`type`,`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
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


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_11_155954_create_fd_rates_table',1),(5,'2026_11_11_155954_create_pages_table',1),(6,'2026_11_11_155955_create_leads_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'product','Fixed Deposit','fixed-deposit','Deposits','Earn assured returns with flexible tenures from 7 days to 10 years.','<p>Earn assured returns with flexible tenures from 7 days to 10 years.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(2,'product','Savings Account','savings-account','Accounts','Zero balance savings account with free digital banking.','<p>Zero balance savings account with free digital banking.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(3,'product','Home Loan','home-loan','Loans','Home loans at attractive interest rates with quick approval.','<p>Home loans at attractive interest rates with quick approval.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(4,'product','Personal Loan','personal-loan','Loans','Instant personal loans with minimal documentation.','<p>Instant personal loans with minimal documentation.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(5,'product','Gold Loan','gold-loan','Loans','Get funds against your gold in under 30 minutes.','<p>Get funds against your gold in under 30 minutes.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(6,'blog','FD vs SIP: Which one is right for you?','fd-vs-sip',NULL,'A simple comparison of safety, returns and liquidity.','<p>A simple comparison of safety, returns and liquidity.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(7,'blog','5 Tips to Improve Your Credit Score','tips-to-improve-credit-score',NULL,'Small habits that make a big difference to your loan eligibility.','<p>Small habits that make a big difference to your loan eligibility.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(8,'branch','Pune - Shivajinagar Branch','pune-shivajinagar',NULL,'FC Road, Shivajinagar, Pune 411005. Mon-Sat, 10 AM - 4 PM.','<p>FC Road, Shivajinagar, Pune 411005. Mon-Sat, 10 AM - 4 PM.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(9,'branch','Mumbai - Andheri East Branch','mumbai-andheri',NULL,'Near Andheri Station, Mumbai 400069. Mon-Sat, 10 AM - 4 PM.','<p>Near Andheri Station, Mumbai 400069. Mon-Sat, 10 AM - 4 PM.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(10,'static','Home','home',NULL,NULL,'<p>Home</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(11,'static','About Us','about-us',NULL,'Serving customers for over 25 years.','<p>Serving customers for over 25 years.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(12,'static','Contact Us','contact-us',NULL,'Call us at 1800-123-4567 or write to care@example.com.','<p>Call us at 1800-123-4567 or write to care@example.com.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(13,'static','Privacy Policy','privacy-policy',NULL,NULL,'<p>Privacy Policy</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(14,'calculator','FD Calculator','fd-calculator',NULL,'Find out the maturity value of your fixed deposit.','<p>Find out the maturity value of your fixed deposit.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(15,'calculator','SIP Calculator','sip-calculator',NULL,'Estimate the future value of your SIP investments.','<p>Estimate the future value of your SIP investments.</p>',1,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(16,'static','Thank You','thank-you',NULL,NULL,'<p>Thank You</p>',1,1,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(17,'product','Car Loan (old page)','car-loan-old',NULL,NULL,'<p>Car Loan (old page)</p>',1,0,NULL,'/products/personal-loan',301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(18,'blog','Festive Offer 2024','festive-offer-2024',NULL,NULL,'<p>Festive Offer 2024</p>',0,0,NULL,NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(19,'product','FD','fd','Deposits',NULL,'<p>FD</p>',1,0,'/products/fixed-deposit',NULL,301,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(20,'blog','Diwali Offer (temporary)','diwali-offer',NULL,NULL,'<p>Diwali Offer (temporary)</p>',1,0,NULL,'/products/home-loan',302,'2026-11-11 11:30:35','2026-11-11 11:30:35');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `fd_rates` WRITE;
/*!40000 ALTER TABLE `fd_rates` DISABLE KEYS */;
INSERT INTO `fd_rates` VALUES (1,7,45,3.50,4.00,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(2,46,179,4.75,5.25,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(3,180,364,6.00,6.50,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(4,365,729,6.80,7.30,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(5,730,1094,7.00,7.50,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(6,1095,1824,6.75,7.25,'2026-11-11 10:59:30','2026-11-11 10:59:30'),(7,1825,3650,6.50,7.25,'2026-11-11 10:59:30','2026-11-11 10:59:30');
/*!40000 ALTER TABLE `fd_rates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

