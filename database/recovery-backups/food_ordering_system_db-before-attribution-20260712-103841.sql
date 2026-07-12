-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: food_ordering_system_db
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
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'System Administrator','admin@savorly.local','$2y$12$cj/doLA6fR829EB78fNOA.5znQxPi21dEwCY5yvpm0LkWqlvPe0vm',1,'2026-07-10 13:19:00','2026-07-10 13:19:00');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `menu_item_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_item` (`customer_id`,`menu_item_id`),
  KEY `fk_cart_menu` (`menu_item_id`),
  CONSTRAINT `fk_cart_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) NOT NULL,
  `first_name` varchar(60) DEFAULT NULL,
  `surname` varchar(60) DEFAULT NULL,
  `username` varchar(60) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `email_verification_token` char(64) DEFAULT NULL,
  `email_verification_expires_at` datetime DEFAULT NULL,
  `email_verification_sent_at` datetime DEFAULT NULL,
  `password_reset_token` char(64) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `password_reset_sent_at` datetime DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone_country` varchar(8) DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `country` varchar(80) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `uq_customer_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (2,'john Doe','john','Doe','johndoe','johndoe@gmail.com','$2y$12$DJGGDy06XLDtknXbhfS25OUEuIpPtPkDRcfED9hBffQv7s7db/bi2','2026-07-11 19:37:45',NULL,NULL,NULL,NULL,NULL,NULL,'123456789','+63','di ko alam','Philippines','1012','2026-07-10 14:16:01','2026-07-11 11:37:45'),(3,'ben ten','ben','ten','benten','benten@gmail.com','$2y$12$5UKwGN/IIfQjGILR9d4aZ.vwQm6DAudF7bXW9GQPsDCgPEhhyuzbS','2026-07-11 19:37:45',NULL,NULL,NULL,NULL,NULL,NULL,'9112345678','+63','1234 di ko lang','Philippines','1008','2026-07-10 17:40:48','2026-07-11 11:37:45'),(4,'Mark Louise Suba','Mark Louise','Suba','suba','423002032@ntc.edu.ph','$2y$12$TUT8YPMa5oOsRChqYMryzOrhDXzkRg20HZFBdrH6KtXjRAl7QofvS','2026-07-11 19:40:54',NULL,NULL,NULL,'fab74dc76ff99e167aa2e9f2046c8e0ba9a54af4c2450e6bab1b6bfa4c03f767','2026-07-11 21:03:50','2026-07-11 20:03:50','123456','+63','di ko alam','Philippines','1012','2026-07-11 11:40:33','2026-07-11 12:03:50'),(5,'Mark Suba','Mark','Suba','suba05','subamarklouise01@gmail.com','$2y$12$0pLQ1TYFTylUR//SC00ouuwOJto70Yu8RH0kSoEhiNVg3kF.cDaZC','2026-07-11 19:42:07',NULL,NULL,NULL,NULL,NULL,NULL,'12345678','+63','ewan 123','Philippines','1012','2026-07-11 11:41:55','2026-07-11 11:42:07'),(6,'Xyryl Sam Manrique','Xyryl Sam','Manrique','xyryl','423005569@ntc.edu.ph','$2y$12$ET5.o0ApK0NksfBv1G5b4eOiXT1Ta/TVcEZLGlpP19CiUClPD14Ju','2026-07-11 23:51:14',NULL,NULL,'2026-07-11 23:50:46',NULL,NULL,NULL,'123456789','+63','basta sa quezon city','Philippines','1012','2026-07-11 11:45:03','2026-07-11 15:51:14'),(7,'Jeremiah Mesa','Jeremiah','Mesa','mesa','61901789@ntc.edu.ph','$2y$12$6.oInWC7qt.9/wP/a.m/3u9bdkdkJWnfhMkRKpGku1Jk3TEoXGqQO','2026-07-11 19:51:23',NULL,NULL,NULL,NULL,NULL,NULL,'123456789','+63','ewan ko','Philippines','1011','2026-07-11 11:47:03','2026-07-11 11:51:23'),(8,'Andrew Ladignon','Andrew','Ladignon','andrew','423000311@ntc.edu.ph','$2y$12$mqAXdsg2Ivl18E8jN51zPe.QjiaA/5bVHFvq/yk.o9Dy.0C0QIk5O','2026-07-11 23:50:13',NULL,NULL,'2026-07-11 23:49:06',NULL,NULL,NULL,'123456789','+63','basta sa baclaran','Philippines','8080','2026-07-11 15:37:59','2026-07-11 15:50:13');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `category` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#e9d5b5',
  `image_path` varchar(255) DEFAULT NULL,
  `badge` varchar(30) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (1,'Truffle Cream Pasta','Pasta','Silky cream sauce, mushrooms, parmesan, and truffle oil.',289.00,'#e9d5b5','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774643/food_ordering_system/products/ywpva1zh5kjkew4txrq5.jpg','BESTSELLER',1,'2026-07-10 13:10:53'),(2,'Crispy Chicken Bowl','Bowls','Golden chicken, garlic rice, fresh greens, and house sauce.',249.00,'#e4c69e','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774723/food_ordering_system/products/jhenjcbzvwu4zgmtc9jz.webp','POPULAR',1,'2026-07-10 13:10:53'),(3,'Garden Pesto Pasta','Pasta','Basil pesto, cherry tomatoes, greens, and toasted seeds.',259.00,'#bfceb0','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774771/food_ordering_system/products/v9wemxfaeksv1nmq5kxn.jpg',NULL,1,'2026-07-10 13:10:53'),(4,'Smoky Beef Burger','Burgers','Smashed beef, cheddar, caramelized onions, and smoky mayo.',279.00,'#dfb792','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774871/food_ordering_system/products/znj8izttdxwkgghhbek2.webp',NULL,1,'2026-07-10 13:10:53'),(5,'Honey Garlic Wings','Sides','Crispy wings glazed with sweet garlic and sesame.',229.00,'#dba980','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774949/food_ordering_system/products/q4anhs9m5zjicc16wrvj.jpg','NEW',1,'2026-07-10 13:10:53'),(6,'Mango Cloud Shake','Drinks','Fresh mango, creamy milk, and a soft whipped finish.',149.00,'#f4ce83','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783775047/food_ordering_system/products/fw0umg379arb8nqvsqqv.jpg',NULL,1,'2026-07-10 13:10:53'),(7,'Classic Tiramisu','Desserts','Espresso-soaked layers with mascarpone and cocoa.',179.00,'#caa889','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783775109/food_ordering_system/products/cji7lh07y6l25g4kblvo.jpg',NULL,1,'2026-07-10 13:10:53'),(8,'Citrus Garden Fizz','Drinks','Calamansi, lemon, sparkling water, and fresh mint.',129.00,'#c8d8a8','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783775099/food_ordering_system/products/vgwx4uhwtdjthetbbyba.jpg',NULL,1,'2026-07-10 13:10:53'),(9,'Four Cheese Pizza','Pizza','Mozzarella, cheddar, parmesan, and creamy blue cheese.',329.00,'#edc989','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783775092/food_ordering_system/products/t7yrz313lkp5k1gmftmc.jpg','POPULAR',1,'2026-07-10 13:39:10'),(10,'Teriyaki Salmon Bowl','Bowls','Glazed salmon, steamed rice, edamame, and sesame.',349.00,'#d6bd9b','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783775083/food_ordering_system/products/p35qrzd8bahqfl2edgon.jpg',NULL,1,'2026-07-10 13:39:10'),(11,'Spicy Chicken Burger','Burgers','Crispy chicken, chili glaze, slaw, and cooling ranch.',269.00,'#d9a074','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783775067/food_ordering_system/products/piul1da0lxiqojd4bcch.webp',NULL,1,'2026-07-10 13:39:10'),(12,'Roasted Tomato Pasta','Pasta','Slow-roasted tomato sauce, garlic, basil, and parmesan.',239.00,'#dca27d','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774730/food_ordering_system/products/x2fmfxma9yolyeyfsbon.jpg',NULL,1,'2026-07-10 13:39:10'),(13,'Loaded Potato Wedges','Sides','Crisp potato wedges, cheese sauce, herbs, and bacon.',189.00,'#eccd8f','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774717/food_ordering_system/products/k9etljy88o0heywxfvnn.jpg',NULL,1,'2026-07-10 13:39:10'),(14,'Matcha Cream Latte','Drinks','Ceremonial matcha, fresh milk, and vanilla cream.',159.00,'#b9c99d','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774707/food_ordering_system/products/ztjbmtb3ozpzi2cycs2o.jpg',NULL,1,'2026-07-10 13:39:10'),(15,'Chocolate Lava Cake','Desserts','Warm chocolate cake with a rich molten center.',199.00,'#b9957d','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774697/food_ordering_system/products/wgflaothe71duevmy8ib.jpg','NEW',1,'2026-07-10 13:39:10'),(16,'Margherita Pizza','Pizza','Tomatoes, fresh mozzarella, basil, and olive oil.',289.00,'#dfb47d','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774689/food_ordering_system/products/tozhjyqeptkr2fcqb0qf.jpg',NULL,1,'2026-07-10 13:39:10'),(17,'Korean Beef Bowl','Bowls','Savory beef, kimchi, steamed rice, and a fried egg.',279.00,'#c69c78','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774676/food_ordering_system/products/m5eoq4cn81xkkqbw0ldx.jpg',NULL,1,'2026-07-10 13:39:10'),(18,'Caesar Garden Salad','Salads','Crisp romaine, parmesan, croutons, and Caesar dressing.',219.00,'#b8c995','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774663/food_ordering_system/products/makumvunq63sdkvgoyxy.jpg',NULL,1,'2026-07-10 13:39:10'),(19,'Strawberry Cheesecake','Desserts','Creamy cheesecake with a bright strawberry topping.',189.00,'#e6b5b2','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774629/food_ordering_system/products/zguge6jbbckudwlbhcot.jpg',NULL,1,'2026-07-10 13:39:10'),(20,'Cold Brew Caramel','Drinks','Slow-steeped coffee, caramel, milk, and soft cream.',160.00,'#c3a486','https://res.cloudinary.com/daeaeo5uy/image/upload/v1783774597/food_ordering_system/products/gaqlxfwaq19zv3qtkazj.jpg',NULL,1,'2026-07-10 13:39:10');
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `menu_item_id` int(10) unsigned DEFAULT NULL,
  `item_name` varchar(140) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_item_order` (`order_id`),
  KEY `fk_item_menu` (`menu_item_id`),
  CONSTRAINT `fk_item_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,'Truffle Cream Pasta',289.00,1),(2,2,1,'Truffle Cream Pasta',289.00,1),(3,2,2,'Crispy Chicken Bowl',249.00,1),(4,2,5,'Honey Garlic Wings',229.00,1),(5,2,9,'Four Cheese Pizza',329.00,1),(6,3,3,'Garden Pesto Pasta',259.00,1),(7,4,20,'Cold Brew Caramel',149.00,1),(8,4,3,'Garden Pesto Pasta',259.00,1),(9,4,12,'Roasted Tomato Pasta',239.00,1),(10,4,2,'Crispy Chicken Bowl',249.00,1),(11,4,10,'Teriyaki Salmon Bowl',349.00,1),(12,4,17,'Korean Beef Bowl',279.00,1),(13,4,11,'Spicy Chicken Burger',269.00,1),(14,4,5,'Honey Garlic Wings',229.00,1),(15,4,13,'Loaded Potato Wedges',189.00,1),(16,4,6,'Mango Cloud Shake',149.00,1),(17,4,8,'Citrus Garden Fizz',129.00,1),(18,4,14,'Matcha Cream Latte',159.00,1),(19,4,1,'Truffle Cream Pasta',289.00,1),(20,5,1,'Truffle Cream Pasta',289.00,5),(21,6,1,'Truffle Cream Pasta',289.00,1),(22,7,1,'Truffle Cream Pasta',289.00,1),(23,7,2,'Crispy Chicken Bowl',249.00,1),(24,7,3,'Garden Pesto Pasta',259.00,1),(25,8,1,'Truffle Cream Pasta',289.00,1),(26,8,2,'Crispy Chicken Bowl',249.00,1),(27,8,3,'Garden Pesto Pasta',259.00,1),(28,9,1,'Truffle Cream Pasta',289.00,1),(29,10,5,'Honey Garlic Wings',229.00,1),(30,10,4,'Smoky Beef Burger',279.00,1);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `status` varchar(40) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_history_order` (`order_id`),
  CONSTRAINT `fk_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
INSERT INTO `order_status_history` VALUES (1,1,'Order placed','2026-07-10 14:18:43'),(2,1,'Preparing','2026-07-10 14:18:59'),(3,1,'Out for delivery','2026-07-10 14:22:25'),(4,1,'Cancelled','2026-07-10 14:25:34'),(5,1,'Out for delivery','2026-07-10 14:25:40'),(6,1,'Delivered','2026-07-10 14:25:42'),(7,1,'Out for delivery','2026-07-10 14:25:43'),(8,1,'Cancelled','2026-07-10 14:25:45'),(9,1,'Order placed','2026-07-10 14:25:52'),(10,1,'Preparing','2026-07-10 14:25:55'),(11,1,'Cancelled','2026-07-10 14:25:56'),(12,1,'Order placed','2026-07-10 14:25:58'),(13,1,'Cancelled','2026-07-10 14:43:33'),(14,1,'Order placed','2026-07-10 14:43:34'),(15,1,'Preparing','2026-07-10 15:16:01'),(16,1,'Order placed','2026-07-10 15:16:03'),(17,1,'Preparing','2026-07-10 15:16:04'),(18,1,'Out for delivery','2026-07-10 15:16:05'),(19,1,'Delivered','2026-07-10 15:16:06'),(20,1,'Cancelled','2026-07-10 15:16:06'),(21,1,'Order placed','2026-07-10 15:16:07'),(22,1,'Preparing','2026-07-10 15:16:08'),(23,1,'Out for delivery','2026-07-10 15:16:08'),(24,1,'Delivered','2026-07-10 15:16:09'),(25,1,'Cancelled','2026-07-10 15:16:12'),(26,1,'Order placed','2026-07-10 15:16:17'),(27,1,'Preparing','2026-07-10 15:18:28'),(28,1,'Out for delivery','2026-07-10 15:18:29'),(29,1,'Delivered','2026-07-10 15:18:29'),(30,1,'Cancelled','2026-07-10 15:18:30'),(31,1,'Order placed','2026-07-10 15:18:31'),(32,1,'Preparing','2026-07-10 15:18:34'),(33,1,'Out for delivery','2026-07-10 15:18:36'),(34,1,'Delivered','2026-07-10 15:18:42'),(35,1,'Cancelled','2026-07-10 15:18:45'),(36,1,'Order placed','2026-07-10 15:18:48'),(37,2,'Order placed','2026-07-10 15:50:46'),(38,1,'Out for delivery','2026-07-10 16:36:02'),(39,1,'Delivered','2026-07-10 16:36:32'),(40,2,'Preparing','2026-07-10 16:51:43'),(41,2,'Out for delivery','2026-07-10 16:51:47'),(42,2,'Delivered','2026-07-10 16:51:50'),(43,2,'Cancelled','2026-07-10 16:51:53'),(44,2,'Order placed','2026-07-10 16:51:55'),(45,2,'Cancelled','2026-07-10 16:51:58'),(46,3,'Order placed','2026-07-10 17:18:34'),(47,4,'Order placed','2026-07-10 17:31:13'),(48,4,'Cancelled','2026-07-10 17:31:28'),(49,3,'Cancelled','2026-07-10 17:31:30'),(50,4,'Delivered','2026-07-10 17:32:40'),(51,4,'Cancelled','2026-07-10 17:32:47'),(52,4,'Delivered','2026-07-10 17:36:20'),(53,2,'Delivered','2026-07-10 17:36:23'),(54,2,'Cancelled','2026-07-10 17:36:26'),(55,5,'Order placed','2026-07-11 11:29:02'),(56,6,'Order placed','2026-07-11 11:30:01'),(57,7,'Order placed','2026-07-11 11:52:44'),(58,8,'Order placed','2026-07-11 11:53:12'),(59,8,'Delivered','2026-07-11 11:57:43'),(60,8,'Order placed','2026-07-11 12:02:01'),(61,8,'Delivered','2026-07-11 12:02:12'),(62,6,'Cancelled','2026-07-11 12:29:21'),(63,5,'Cancelled','2026-07-11 12:29:22'),(64,9,'Order placed','2026-07-11 16:00:53'),(65,10,'Order placed','2026-07-11 16:01:07'),(66,10,'Preparing','2026-07-11 16:01:36'),(67,10,'Out for delivery','2026-07-11 16:01:49'),(68,10,'Delivered','2026-07-11 16:02:00'),(69,9,'Cancelled','2026-07-12 02:38:20');
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(30) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `payment_method` varchar(60) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 49.00,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Order placed','Preparing','Out for delivery','Delivered','Cancelled') NOT NULL DEFAULT 'Order placed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delivered_email_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_order_customer` (`customer_id`),
  CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'260710-68318A',2,'di ko alam, 1012, Philippines','63123456789','Cash on delivery','',289.00,49.00,338.00,'Delivered','2026-07-10 14:18:43','2026-07-10 16:36:32',NULL),(2,'260710-30F086',2,'di ko alam, 1012, Philippines','63123456789','Cash on delivery','',1096.00,49.00,1145.00,'Cancelled','2026-07-10 15:50:46','2026-07-10 17:36:26',NULL),(3,'260711-F5C13F',2,'di ko alam, 1012, Philippines','63123456789','Cash on delivery','',259.00,49.00,308.00,'Cancelled','2026-07-10 17:18:34','2026-07-10 17:31:30',NULL),(4,'260711-F3921F',2,'di ko alam, 1012, Philippines','63123456789','Cash on delivery','',2937.00,49.00,2986.00,'Delivered','2026-07-10 17:31:13','2026-07-10 17:36:20',NULL),(5,'260711-1C3C06',2,'di ko alam, 1012, Philippines','63123456789','Cash on delivery','',1445.00,49.00,1494.00,'Cancelled','2026-07-11 11:29:02','2026-07-11 12:29:22',NULL),(6,'260711-08C27B',2,'di ko alam, 1012, Philippines','63123456789','Cash on delivery','',289.00,49.00,338.00,'Cancelled','2026-07-11 11:30:01','2026-07-11 12:29:21',NULL),(7,'260711-353C9F',7,'ewan ko, 1011, Philippines','63123456789','Cash on delivery','',797.00,49.00,846.00,'Order placed','2026-07-11 11:52:44','2026-07-11 11:52:44',NULL),(8,'260711-D29DF7',4,'di ko alam, 1012, Philippines','63123456','Cash on delivery','',797.00,49.00,846.00,'Delivered','2026-07-11 11:53:12','2026-07-11 12:02:12','2026-07-11 20:02:12'),(9,'260712-504F88',4,'di ko alam, 1012, Philippines','63123456','Cash on delivery','',289.00,49.00,338.00,'Cancelled','2026-07-11 16:00:53','2026-07-12 02:38:20',NULL),(10,'260712-57355B',8,'basta sa baclaran, 8080, Philippines','63123456789','Cash on delivery','',508.00,49.00,557.00,'Delivered','2026-07-11 16:01:07','2026-07-11 16:02:00','2026-07-12 00:02:00');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_users`
--

DROP TABLE IF EXISTS `staff_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_users`
--

LOCK TABLES `staff_users` WRITE;
/*!40000 ALTER TABLE `staff_users` DISABLE KEYS */;
INSERT INTO `staff_users` VALUES (2,'Kitchen Staff','staff@savorly.local','$2y$12$MYIocFuxq8be1Bcb8JFcEu3VSHeGd3H9xAaTgaAoRrye0flSp.Lj.',1,'2026-07-10 13:16:32','2026-07-10 13:16:32');
/*!40000 ALTER TABLE `staff_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'food_ordering_system_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-12 10:38:42
