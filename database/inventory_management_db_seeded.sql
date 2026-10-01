-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: inventory_management_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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

--
-- Current Database: `inventory_management_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `inventory_management_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `inventory_management_db`;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_user` (`user_id`),
  KEY `idx_activity_action` (`action`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_type` enum('username','ip') NOT NULL,
  `identifier_hash` char(64) NOT NULL,
  `attempt_count` int(10) unsigned NOT NULL DEFAULT 0,
  `window_started_at` datetime NOT NULL,
  `locked_until` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_login_attempt_scope` (`scope_type`,`identifier_hash`),
  KEY `idx_login_attempts_locked_until` (`locked_until`),
  KEY `idx_login_attempts_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'LOGIN','Administrator signed in','127.0.0.1','2026-09-29 00:00:00','2026-09-29 00:00:00'),(2,2,'LOGIN','Inventory staff signed in','127.0.0.1','2026-09-29 00:10:00','2026-09-29 00:10:00'),(3,1,'PRODUCT_CREATED','Created product ELEC-004 USB-C Hub 6-in-1','127.0.0.1','2026-01-08 01:00:00','2026-01-08 01:00:00'),(4,1,'STOCK_IN','Processed SIN-2026-0929','127.0.0.1','2026-09-29 00:30:00','2026-09-29 00:30:00'),(5,2,'STOCK_OUT','Processed SOUT-2026-0929','127.0.0.1','2026-09-29 05:30:00','2026-09-29 05:30:00'),(6,1,'stock_adjustment','Processed ADJUSTMENT-2026-0927-0001','127.0.0.1','2026-09-27 02:00:00','2026-09-27 02:00:00'),(7,1,'REPORT_EXPORT','Exported movement report for Sep 1-29, 2026','127.0.0.1','2026-09-29 06:00:00','2026-09-29 06:00:00');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'General',1,'2026-09-29 13:15:52','2025-11-02 01:00:00'),(2,'Electronics',1,'2026-09-29 13:15:52','2025-11-02 01:01:00'),(3,'Office Supplies',1,'2026-09-29 13:15:52','2025-11-02 01:02:00'),(4,'Computer Accessories',1,'2025-11-02 01:03:00','2025-11-02 01:03:00'),(5,'Cleaning Supplies',1,'2025-11-02 01:04:00','2025-11-02 01:04:00'),(6,'Pantry',1,'2025-11-02 01:05:00','2025-11-02 01:05:00'),(7,'Archived Supplies',0,'2025-11-02 01:06:00','2025-11-02 01:06:00');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(100) NOT NULL,
  `module_key` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_modules_name` (`module_name`),
  UNIQUE KEY `uq_modules_key` (`module_key`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'Dashboard','dashboard','Dashboard overview',1,10,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(2,'Products','products','Product management',1,20,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(3,'Categories','categories','Category management',1,30,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(4,'Suppliers','suppliers','Supplier management',1,40,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(5,'Stock','stock','Inventory stock management',1,50,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(6,'Reports','reports','Inventory reports',1,60,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(7,'Users','users','User management',1,70,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(8,'Roles','roles','Role and permission management',1,80,'2026-09-29 13:15:52','2025-11-01 00:10:00'),(9,'Activity Logs','activity_logs','Audit and activity history',1,75,'2026-10-01 06:55:00','2026-10-01 06:55:00');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_key` (`permission_key`),
  UNIQUE KEY `uq_module_action` (`module_id`,`action`),
  KEY `idx_permissions_module` (`module_id`),
  CONSTRAINT `fk_permissions_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,1,'View Dashboard','dashboard.view','view','View dashboard overview',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(2,2,'View Products','products.view','view','View products',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(3,2,'Create Products','products.create','create','Add products',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(4,2,'Edit Products','products.edit','edit','Edit products',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(5,2,'Delete Products','products.delete','delete','Delete products',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(6,3,'View Categories','categories.view','view','View categories',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(7,3,'Create Categories','categories.create','create','Add categories',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(8,3,'Edit Categories','categories.edit','edit','Edit categories',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(9,3,'Delete Categories','categories.delete','delete','Delete categories',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(10,4,'View Suppliers','suppliers.view','view','View suppliers',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(11,4,'Create Suppliers','suppliers.create','create','Add suppliers',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(12,4,'Edit Suppliers','suppliers.edit','edit','Edit suppliers',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(13,4,'Delete Suppliers','suppliers.delete','delete','Delete suppliers',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(14,5,'View Stock','stock.view','view','View current stock',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(15,5,'Stock In','stock.stock_in','stock_in','Process stock-in transactions',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(16,5,'Stock Out','stock.stock_out','stock_out','Process stock-out transactions',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(17,5,'Adjust Stock','stock.adjust','adjust','Adjust inventory records',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(18,5,'View Stock History','stock.history','history','View stock transaction history',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(19,6,'View Reports','reports.view','view','View reports',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(20,6,'Export Reports','reports.export','export','Export reports',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(21,7,'View Users','users.view','view','View system users',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(22,7,'Create Users','users.create','create','Create system users',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(23,7,'Edit Users','users.edit','edit','Edit system users',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(24,7,'Delete Users','users.delete','delete','Delete system users',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(25,8,'View Roles','roles.view','view','View roles and assigned permissions',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(26,8,'Create Roles','roles.create','create','Create system roles',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(27,8,'Edit Roles','roles.edit','edit','Edit role information',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(28,8,'Delete Roles','roles.delete','delete','Delete unused roles',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(29,8,'Manage Role Permissions','roles.permissions','permissions','Assign or remove permissions from roles',1,'2026-09-29 13:15:52','2025-11-01 00:20:00'),(30,9,'View Activity Logs','activity_logs.view','view','View system activity and audit logs',1,'2026-10-01 06:55:00','2026-10-01 06:55:00');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_code` (`product_code`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_supplier` (`supplier_id`),
  KEY `idx_products_name` (`product_name`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,1,'GEN-001','Ballpen - Blue','box',120.00,180.00,130,30,1,'2025-11-10 01:00:00','2026-09-29 02:00:00'),(2,1,1,'GEN-002','Notebook - A5','pack',75.00,110.00,15,20,1,'2025-11-10 01:05:00','2026-09-20 02:00:00'),(3,1,NULL,'GEN-003','Packing Tape - Clear','roll',45.00,70.00,0,10,1,'2025-12-02 01:00:00','2026-09-15 02:00:00'),(4,2,2,'ELEC-001','USB Keyboard','piece',650.00,899.00,13,5,1,'2025-11-12 01:00:00','2026-04-17 02:00:00'),(5,2,2,'ELEC-002','Wireless Mouse','piece',420.00,699.00,6,8,1,'2025-11-12 01:05:00','2026-04-17 02:00:00'),(6,2,2,'ELEC-003','HDMI Cable 2m','piece',350.00,550.00,17,5,1,'2025-11-12 01:10:00','2026-09-27 02:00:00'),(7,4,NULL,'ELEC-004','USB-C Hub 6-in-1','piece',950.00,1399.00,9,4,1,'2026-01-08 01:00:00','2026-09-15 02:00:00'),(8,3,3,'OFF-001','Bond Paper A4','ream',210.00,285.00,55,10,1,'2025-11-15 01:00:00','2026-06-18 02:00:00'),(9,3,3,'OFF-002','Stapler - Standard','piece',95.00,145.00,4,5,1,'2025-11-15 01:05:00','2026-09-20 02:00:00'),(10,5,4,'CLN-001','Multi-Purpose Cleaner','bottle',135.00,195.00,0,10,1,'2025-11-20 01:00:00','2026-08-06 02:00:00'),(11,5,4,'CLN-002','Tissue Paper','pack',85.00,125.00,60,15,1,'2025-11-20 01:05:00','2026-08-06 02:00:00'),(12,6,5,'PAN-001','Bottled Water 500ml','case',180.00,240.00,100,25,1,'2025-12-01 01:00:00','2026-08-28 02:00:00'),(13,6,5,'PAN-002','Coffee 3-in-1','box',145.00,210.00,40,10,1,'2025-12-01 01:05:00','2026-08-28 02:00:00'),(14,7,6,'GEN-004','Legacy Filing Labels','pack',60.00,90.00,0,5,0,'2025-11-25 01:00:00','2026-08-01 02:00:00');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_permission` (`role_id`,`permission_id`),
  KEY `idx_role_permissions_role` (`role_id`),
  KEY `idx_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1,1,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(2,1,2,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(3,1,3,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(4,1,4,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(5,1,5,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(6,1,6,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(7,1,7,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(8,1,8,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(9,1,9,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(10,1,10,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(11,1,11,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(12,1,12,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(13,1,13,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(14,1,14,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(15,1,15,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(16,1,16,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(17,1,17,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(18,1,18,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(19,1,19,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(20,1,20,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(21,1,21,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(22,1,22,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(23,1,23,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(24,1,24,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(25,1,25,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(26,1,26,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(27,1,27,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(28,1,28,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(29,1,29,'2026-09-29 13:15:52','2025-11-01 00:30:00'),(30,1,30,'2026-10-01 06:55:00','2026-10-01 06:55:00'),(32,2,6,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(33,2,1,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(34,2,2,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(35,2,19,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(36,2,18,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(37,2,15,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(38,2,16,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(39,2,14,'2026-09-29 13:15:52','2025-11-01 00:31:00'),(40,2,10,'2026-09-29 13:15:52','2025-11-01 00:31:00');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Full system access',1,'2026-09-29 13:15:52','2025-11-01 00:00:00'),(2,'staff','Limited inventory access',1,'2026-09-29 13:15:52','2025-11-01 00:05:00');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustments`
--

DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_adjustments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `system_stock` int(11) NOT NULL,
  `actual_stock` int(11) NOT NULL,
  `difference` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stock_adjustments_transaction` (`transaction_id`),
  KEY `idx_adjustments_product` (`product_id`),
  KEY `idx_adjustments_user` (`created_by`),
  CONSTRAINT `fk_stock_adjustments_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_adjustments_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustments`
--

LOCK TABLES `stock_adjustments` WRITE;
/*!40000 ALTER TABLE `stock_adjustments` DISABLE KEYS */;
INSERT INTO `stock_adjustments` VALUES (1,NULL,9,3,4,1,'Physical count found one extra stapler',1,'2026-09-20 02:00:00','2026-09-20 02:00:00'),(2,NULL,6,18,17,-1,'Damaged HDMI cable found during count',1,'2026-09-27 02:00:00','2026-09-27 02:00:00');
/*!40000 ALTER TABLE `stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transaction_items`
--

DROP TABLE IF EXISTS `stock_transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transaction_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_items_transaction` (`transaction_id`),
  KEY `idx_items_product` (`product_id`),
  CONSTRAINT `fk_transaction_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_transaction_items_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `stock_transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transaction_items`
--

LOCK TABLES `stock_transaction_items` WRITE;
/*!40000 ALTER TABLE `stock_transaction_items` DISABLE KEYS */;
INSERT INTO `stock_transaction_items` VALUES (1,1,1,160,120.00,'2025-11-12 02:00:00','2025-11-12 02:00:00'),(2,1,2,45,75.00,'2025-11-12 02:00:00','2025-11-12 02:00:00'),(3,2,1,20,120.00,'2025-12-04 06:00:00','2025-12-04 06:00:00'),(4,2,2,10,75.00,'2025-12-04 06:00:00','2025-12-04 06:00:00'),(5,3,1,30,120.00,'2026-01-08 01:30:00','2026-01-08 01:30:00'),(6,3,2,10,75.00,'2026-01-08 01:30:00','2026-01-08 01:30:00'),(7,4,1,40,120.00,'2026-02-19 07:15:00','2026-02-19 07:15:00'),(8,4,2,30,75.00,'2026-02-19 07:15:00','2026-02-19 07:15:00'),(9,5,4,30,650.00,'2026-03-03 03:00:00','2026-03-03 03:00:00'),(10,5,5,20,420.00,'2026-03-03 03:00:00','2026-03-03 03:00:00'),(11,5,6,18,350.00,'2026-03-03 03:00:00','2026-03-03 03:00:00'),(12,6,4,17,650.00,'2026-04-17 05:45:00','2026-04-17 05:45:00'),(13,6,5,14,420.00,'2026-04-17 05:45:00','2026-04-17 05:45:00'),(14,7,8,60,210.00,'2026-05-09 00:45:00','2026-05-09 00:45:00'),(15,7,9,8,95.00,'2026-05-09 00:45:00','2026-05-09 00:45:00'),(16,8,8,5,210.00,'2026-06-18 08:00:00','2026-06-18 08:00:00'),(17,8,9,5,95.00,'2026-06-18 08:00:00','2026-06-18 08:00:00'),(18,9,10,25,135.00,'2026-07-22 02:15:00','2026-07-22 02:15:00'),(19,9,11,70,85.00,'2026-07-22 02:15:00','2026-07-22 02:15:00'),(20,10,10,25,135.00,'2026-08-06 07:30:00','2026-08-06 07:30:00'),(21,10,11,10,85.00,'2026-08-06 07:30:00','2026-08-06 07:30:00'),(22,11,12,120,180.00,'2026-08-12 01:00:00','2026-08-12 01:00:00'),(23,11,13,50,145.00,'2026-08-12 01:00:00','2026-08-12 01:00:00'),(24,12,12,20,180.00,'2026-08-28 09:00:00','2026-08-28 09:00:00'),(25,12,13,10,145.00,'2026-08-28 09:00:00','2026-08-28 09:00:00'),(26,13,7,12,950.00,'2026-09-01 01:00:00','2026-09-01 01:00:00'),(27,13,3,25,45.00,'2026-09-01 01:00:00','2026-09-01 01:00:00'),(28,14,7,3,950.00,'2026-09-15 03:30:00','2026-09-15 03:30:00'),(29,14,3,25,45.00,'2026-09-15 03:30:00','2026-09-15 03:30:00'),(30,15,9,1,95.00,'2026-09-20 02:00:00','2026-09-20 02:00:00'),(31,16,6,1,350.00,'2026-09-27 02:00:00','2026-09-27 02:00:00'),(32,17,1,5,120.00,'2026-09-29 00:30:00','2026-09-29 00:30:00'),(33,18,1,5,120.00,'2026-09-29 05:30:00','2026-09-29 05:30:00');
/*!40000 ALTER TABLE `stock_transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

ALTER TABLE stock_transaction_items
  ADD COLUMN product_code_snapshot varchar(50) DEFAULT NULL,
  ADD COLUMN product_name_snapshot varchar(150) DEFAULT NULL,
  ADD COLUMN category_id_snapshot int(11) DEFAULT NULL,
  ADD COLUMN category_name_snapshot varchar(100) DEFAULT NULL,
  ADD COLUMN unit_snapshot varchar(50) DEFAULT NULL,
  ADD COLUMN metadata_snapshot_source enum('transaction','reconstructed') NOT NULL DEFAULT 'reconstructed',
  ADD KEY idx_items_category_snapshot (category_id_snapshot);

UPDATE stock_transaction_items i
JOIN products p ON p.id = i.product_id
JOIN categories c ON c.id = p.category_id
SET i.product_code_snapshot = p.product_code,
    i.product_name_snapshot = p.product_name,
    i.category_id_snapshot = p.category_id,
    i.category_name_snapshot = c.category_name,
    i.unit_snapshot = p.unit,
    i.metadata_snapshot_source = 'reconstructed';

--
-- Table structure for table `stock_transactions`
--

DROP TABLE IF EXISTS `stock_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_no` varchar(50) NOT NULL,
  `type` enum('stock_in','stock_out','adjustment') NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transaction_no` (`transaction_no`),
  KEY `idx_transactions_type` (`type`),
  KEY `idx_transactions_supplier` (`supplier_id`),
  KEY `idx_transactions_created_by` (`created_by`),
  CONSTRAINT `fk_stock_transactions_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_transactions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `fk_stock_adjustments_transaction`
  FOREIGN KEY (`transaction_id`) REFERENCES `stock_transactions` (`id`)
  ON UPDATE CASCADE ON DELETE RESTRICT;

--
-- Dumping data for table `stock_transactions`
--

LOCK TABLES `stock_transactions` WRITE;
/*!40000 ALTER TABLE `stock_transactions` DISABLE KEYS */;
INSERT INTO `stock_transactions` VALUES (1,'SIN-2025-1101','stock_in',1,'Initial office supplies replenishment',1,'2025-11-12 02:00:00','2025-11-12 02:00:00'),(2,'SOUT-2025-1204','stock_out',1,'Issued to front desk and training room',2,'2025-12-04 06:00:00','2025-12-04 06:00:00'),(3,'SIN-2026-0108','stock_in',1,'January replenishment',1,'2026-01-08 01:30:00','2026-01-08 01:30:00'),(4,'SOUT-2026-0219','stock_out',1,'Issued for onboarding kits',2,'2026-02-19 07:15:00','2026-02-19 07:15:00'),(5,'SIN-2026-0303','stock_in',2,'IT equipment delivery',1,'2026-03-03 03:00:00','2026-03-03 03:00:00'),(6,'SOUT-2026-0417','stock_out',2,'Issued to support team',2,'2026-04-17 05:45:00','2026-04-17 05:45:00'),(7,'SIN-2026-0509','stock_in',3,'Office paper and stapler delivery',1,'2026-05-09 00:45:00','2026-05-09 00:45:00'),(8,'SOUT-2026-0618','stock_out',3,'Monthly office consumption',2,'2026-06-18 08:00:00','2026-06-18 08:00:00'),(9,'SIN-2026-0722','stock_in',4,'Cleaning supplies delivery',1,'2026-07-22 02:15:00','2026-07-22 02:15:00'),(10,'SOUT-2026-0806','stock_out',4,'Facilities usage',2,'2026-08-06 07:30:00','2026-08-06 07:30:00'),(11,'SIN-2026-0812','stock_in',5,'Pantry replenishment',1,'2026-08-12 01:00:00','2026-08-12 01:00:00'),(12,'SOUT-2026-0828','stock_out',5,'Pantry weekly issue',2,'2026-08-28 09:00:00','2026-08-28 09:00:00'),(13,'SIN-2026-0901','stock_in',NULL,'Unassigned Products receipt',1,'2026-09-01 01:00:00','2026-09-01 01:00:00'),(14,'SOUT-2026-0915','stock_out',NULL,'Unassigned Products issue',2,'2026-09-15 03:30:00','2026-09-15 03:30:00'),(15,'ADJUSTMENT-2026-0920-0001','adjustment',NULL,'Physical count found one extra stapler',1,'2026-09-20 02:00:00','2026-09-20 02:00:00'),(16,'ADJUSTMENT-2026-0927-0001','adjustment',NULL,'Damaged HDMI cable found during count',1,'2026-09-27 02:00:00','2026-09-27 02:00:00'),(17,'SIN-2026-0929','stock_in',1,'Same-day emergency replenishment',1,'2026-09-29 00:30:00','2026-09-29 00:30:00'),(18,'SOUT-2026-0929','stock_out',1,'Same-day issue to operations',2,'2026-09-29 05:30:00','2026-09-29 05:30:00');
/*!40000 ALTER TABLE `stock_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(150) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_suppliers_name` (`supplier_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Cebu Office Solutions','Juan Dela Cruz','09171234567','Cebu City, Cebu, Philippines',1,'2025-11-03 01:00:00','2025-11-03 01:00:00'),(2,'TechSource Philippines','Maria Santos','09181234567','Mandaue City, Cebu, Philippines',1,'2025-11-03 01:01:00','2025-11-03 01:01:00'),(3,'Visayas General Trading','Pedro Garcia','09191234567','Lapu-Lapu City, Cebu, Philippines',1,'2025-11-03 01:02:00','2025-11-03 01:02:00'),(4,'Prime Cleaning Supplies','Ana Reyes','09201234567','Cebu City, Cebu, Philippines',1,'2025-11-03 01:03:00','2025-11-03 01:03:00'),(5,'Pacific Pantry Supply','Carlos Ramos','09211234567','Mandaue City, Cebu, Philippines',1,'2025-11-03 01:04:00','2025-11-03 01:04:00'),(6,'Legacy Office Supplier','Ramon Cruz','09221234567','Cebu City, Cebu, Philippines',0,'2025-11-03 01:05:00','2025-11-03 01:05:00');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `role_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System',NULL,'Administrator','admin','$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO',0,1,1,'2025-11-04 00:00:00','2025-11-04 00:00:00'),(2,'Inventory',NULL,'Staff','staff','$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO',0,2,1,'2025-11-04 00:05:00','2025-11-04 00:05:00'),(3,'Casey','M.','Auditor','auditor','$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO',1,2,1,'2026-01-15 02:00:00','2026-01-15 02:00:00'),(4,'Former',NULL,'Staff','former_staff','$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO',0,2,0,'2026-02-10 02:00:00','2026-08-01 02:00:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

ALTER TABLE `users`
  ADD COLUMN `auth_version` int(10) unsigned NOT NULL DEFAULT 1;

ALTER TABLE `users`
  ADD COLUMN `profile_image` varchar(255) DEFAULT NULL;

--
-- Dumping routines for database 'inventory_management_db'
--
UPDATE stock_adjustments a
JOIN stock_transactions t
  ON t.transaction_no = CASE a.reason
      WHEN 'Physical count found one extra stapler' THEN 'ADJUSTMENT-2026-0920-0001'
      WHEN 'Damaged HDMI cable found during count' THEN 'ADJUSTMENT-2026-0927-0001'
      ELSE ''
  END
JOIN stock_transaction_items i
  ON i.transaction_id = t.id AND i.product_id = a.product_id
SET a.transaction_id = t.id
WHERE a.transaction_id IS NULL
  AND t.type = 'adjustment'
  AND t.created_by = a.created_by
  AND t.remarks = a.reason;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-29 21:16:04
