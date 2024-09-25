-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: delivery
-- ------------------------------------------------------
-- Server version	8.0.30

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
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `OrderId` int NOT NULL AUTO_INCREMENT,
  `ClientID` int NOT NULL,
  `Address` varchar(150) DEFAULT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Status` enum('Order Received','Order Ready','Picked up','Pending','Delivered','Canceled') DEFAULT NULL,
  `StatusUpdateAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DriverId` int DEFAULT NULL,
  `price` float DEFAULT NULL,
  PRIMARY KEY (`OrderId`),
  KEY `ClientID` (`ClientID`),
  KEY `fk_driver` (`DriverId`),
  CONSTRAINT `fk_driver` FOREIGN KEY (`DriverId`) REFERENCES `user` (`ID`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`ClientID`) REFERENCES `user` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (158,5,'Mauk Lek','2024-09-24 22:26:01','Order Received','2024-09-24 22:52:57',18,350),(159,6,'Mauk Lek','2024-09-24 22:32:14',NULL,'2024-09-24 22:32:14',NULL,350),(160,6,'Mauk Lek','2024-09-24 22:32:20',NULL,'2024-09-24 22:32:20',NULL,350),(161,6,'Bang Kok','2024-09-24 22:34:39',NULL,'2024-09-24 22:34:39',NULL,1000),(162,6,'Bang Kok','2024-09-24 22:43:11',NULL,'2024-09-24 22:43:11',NULL,1000),(163,17,'Chaing Mai','2024-09-24 22:50:30',NULL,'2024-09-24 22:50:30',5,2000),(164,17,'Inn Lay','2024-09-24 22:50:53',NULL,'2024-09-24 22:50:53',NULL,200);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `Age` int DEFAULT NULL,
  `Username` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `CreatedTime` datetime DEFAULT CURRENT_TIMESTAMP,
  `Permission` enum('admin','client','driver') NOT NULL,
  `AvailabilityStatus` enum('on','off') DEFAULT 'off',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Username` (`Username`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Username_2` (`Username`),
  UNIQUE KEY `Email_2` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (5,'Lu Soe Lay',21,'bobo','yopae@gmail.com','$2y$10$CjvKb3Ts1TMhz9RxUdFP8.ZZDMqrkdI2.u9unNtZ4mPGZlxIKSmxS','2024-09-19 22:59:38','driver','on'),(6,'KoYoLay',21,'Naruto','johannhla7777@gmail.com','$2y$10$baL5MrGFu9LcOd6BpwqdGem6WO5FMU8XzhXN4abL2C9adVvQzq07q','2024-09-20 13:08:58','client','off'),(7,'phillip',22,'phillip123','phillip@gmail.com','$2y$10$NOur6.bbsrj2EpqrDm7QY.H.3b2BY2GbjuF5ajN5vHZNEfeXgKuR2','2024-09-23 15:14:30','client','off'),(10,'Itachi',21,'tsawlay','tzah2003@gmail.com','$2y$10$7wAad/c4QlBsVCz/XAP5eOZ1imW5u9I0tGSw0879dQPHAJHyhbAfC','2024-09-23 19:27:44','driver','off'),(11,'Hashirama',21,'YOhan','gg@gmail.com','$2y$10$crAHGkSwtU84vzWz1Spl/.jrAxFOXIITzRQEiLHYZvDn/vV8xKuP2','2024-09-24 10:45:43','client','off'),(12,'Tsunade',51,'5th Hokage','tsutsu@gmail.com','$2y$10$RzgqIPMnRAkHtftktNZqG.sEHRr8TUAzq5RoggalxytUcWBwdl5tC','2024-09-24 10:46:24','driver','off'),(16,'Johann',21,'Twenty1dope','admin@gmail.com','$2y$10$k4MQbg4hcEXCZ5A15GYIEe.ysVuYAvhHOBZ/xmwS9bDx2/hBaljpS','2024-09-24 11:04:28','admin','on'),(17,'Madara',17,'UchihaXSenju','madara@gmail.com','$2y$10$WgvY1q6fi5A6Rx//5/huYO069nHTcaEweAAhKYuuVmCUefJCE0N8G','2024-09-24 11:10:01','client','off'),(18,'Sasuke',90,'NarutoBestfriend','sasuke@gmail.com','$2y$10$dUExzOoCzU9x.W6sLYlesuijOCvxAAB.9m/8JSIys8KzV8XaqreP6','2024-09-24 15:31:40','driver','on'),(19,'Gaara',67,'Sandman','gaara@gmail.com','$2y$10$2Fe5A7kt0mkctktnseU63uvRQY6jQ/mFYKaaikkSTPNeY6g4TJ9Iu','2024-09-24 15:32:28','client','off'),(20,'Sakura',12,'NarutoFirstLove','sakura@gmail.com','$2y$10$2J3hC6VGq8JejjU9ac.SfeVV6bg4a1gc3EIs3qg/7aZs9rOxhNbZy','2024-09-24 15:33:48','client','off'),(21,'Jonathan',20,'jforApple','jonathan@gmail.com','$2y$10$KS4DWMwkFC6wcrkArrlzQO0JTc158DLK/GhL/DmUCefTDQjty7dy.','2024-09-25 01:31:57','client','off'),(22,'Jonathan',20,'jforApple23','jonathan69@gmail.com','$2y$10$zMEKShrYhYLiVlaKLCfLheyl3drD7Zt7IwEpDEWvoKCh2FGvv6TBG','2024-09-25 01:32:26','driver','off');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-25  6:29:37
