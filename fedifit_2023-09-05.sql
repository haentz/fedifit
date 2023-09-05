# ************************************************************
# Sequel Ace SQL dump
# Version 20050
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: 10.0.1.94 (MySQL 8.0.34-0ubuntu0.22.04.1)
# Database: fedifit
# Generation Time: 2023-09-05 05:47:28 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table tactivity
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tactivity`;

CREATE TABLE `tactivity` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `creationdate` datetime NOT NULL,
  `fkiduser` int unsigned NOT NULL,
  `strava_activity_id` bigint unsigned NOT NULL,
  `heroImage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `text` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `released` tinyint(1) NOT NULL,
  `downloaded` tinyint(1) NOT NULL,
  `like` int NOT NULL,
  `announce` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_strava_activity_id` (`strava_activity_id`),
  UNIQUE KEY `unq_heroimage` (`heroImage`),
  KEY `iduser` (`fkiduser`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `tactivity` WRITE;
/*!40000 ALTER TABLE `tactivity` DISABLE KEYS */;

INSERT INTO `tactivity` (`id`, `creationdate`, `fkiduser`, `strava_activity_id`, `heroImage`, `text`, `released`, `downloaded`, `like`, `announce`)
VALUES
	(173,'2023-06-27 14:34:11',6,9256471211,'71e0d5faf78cf4a68edf232d5d1788a0.jpg','Kochel -  Hello Herzogstand 🥵',1,1,0,0),
	(174,'2023-06-27 14:34:28',6,9193101269,'97fb854f31f7dcdb1b93324f02ea280f.jpg','Augsburg -  Morning Gravel Ride',1,1,0,0),
	(175,'2023-06-27 17:37:01',6,9332359898,'69acf5a80d5e26b8945697c270ff3fba.jpg','Isartrails mit dem Jasper 😁',1,1,0,0);

/*!40000 ALTER TABLE `tactivity` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tfollow
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tfollow`;

CREATE TABLE `tfollow` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `fkiduser` int unsigned NOT NULL,
  `actor` varchar(255) NOT NULL,
  `actordomain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `creationdate` datetime NOT NULL,
  `publickey` varchar(451) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fkiduser` (`fkiduser`),
  KEY `actordomain` (`actordomain`),
  CONSTRAINT `activity-fkiduser` FOREIGN KEY (`fkiduser`) REFERENCES `tuser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



# Dump of table tkeys
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tkeys`;

CREATE TABLE `tkeys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `fkiduser` int unsigned NOT NULL,
  `privatekey` varchar(1704) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `publickey` varchar(451) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fkiduser` (`fkiduser`),
  CONSTRAINT `fkiduser` FOREIGN KEY (`fkiduser`) REFERENCES `tuser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `tkeys` WRITE;
/*!40000 ALTER TABLE `tkeys` DISABLE KEYS */;

INSERT INTO `tkeys` (`id`, `fkiduser`, `privatekey`, `publickey`)
VALUES
	(1,6,'-----BEGIN PRIVATE KEY----- MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDxb7bDQmjWgLxM b21pKoXy3f4lrRLb9O9OvHnlCk9NCdh3T65XlF5uNBPSPahPbGDwMr/65xNNr33+ 4QjyDurvyFBJ5a7JCb/bnYMwC0ATSilyHZ7poOq8kNot6SZ1AYEJflJHCBabtkDE Pi4ED5jadQhVkRWf+4Ryjt13SfwnSTydXRvt3Z5MEipFBTVXyS9uBSk9o+T153VU uUW69p+ceu2JsCIK8bJYKmRnDTX/4iYm4aXIBLxtA8LMEOrMsUTp5mSK2v9gVKhd l7kRsmtQeJibgg1ijFBQtr8xN0m4wXsniN7iQokyUe28bIjDNAYRQZ/Id452W0Z+ 47pZ3xjxAgMBAAECggEATQRf/sWuf86tQozBEC06cZETQ52ESJpHxIwnLkMtrNuZ K+ZFLTbtMKZ4NDbq+/sMqVLnHviblcqHnMjYe676jucNhaU5LnRnuXzznl3YYM7l jbuZN9nXR7PFhaO8WtLSAN9FC9eurpli67cSIsV+8yEmOXWAunoXdBvS1fzJ7RW3 GcQMPzmYOoMy94zrKsDxosrIy5ml2rxyryznOHJ0zSc+fmnISI38/m7jsYbwePMG NV7+48TH0VxZn8TRMBOJt4EXErfgIypwqRqeM1/SPLJBlEFftVP3R4kWfjxsXGws /2nNFNk0U2BodsbaOoKR4nFoj2sJTWUjMlcCufdEAQKBgQD7zSOWHGRJ0hi+/BlO vtr5pcYx3gJLQo1/XkK11OVR33zix5OgGBNt/toqsAU3ROKvPBYcNQIx5EvMQGfA cRct9LNDBlyVDRENeKKjbU3Yx0uqzNKnQInlblVQx/N8wr4/ouFkYokaruNopIv1 ATvN09IPJ2iR7VyExqVTMkBkIQKBgQD1dlSJIiXZKkO0k3KdWZ76cdzQKNDftuYa XEBC+1RRr560XBSKJh+pkDK8vAUB15Wmq0BzxK5Mct726VT0hD9Ug60P/BHWj5PK EZzN9VV9sCJL8WBq05HzCJTWL6CWjptZvp0WpJRD3kIe1KJuqYrwGlMxTTtz2RLu AUvu5OIa0QKBgCuM7HW/DV1zOBJ4OcxT2D3LfT8MverUZPK9k94tJ0iE1e3HRPIw l9Oze3pcnVpU0LwsMUrJpEqKyM1X6lnvdWneY87Y39RrwSJKVh7b/dXbimvNJzYE RJVpAtqI6sUOTadl/YPGQx5ZVz23D/ndL8HGvBMg5sYufoz8aoW8METhAoGAd8/D YZBmcwLbO2gBOObG3632PzC9C4Wl+VXwFAHDXX/gcl7Ds876A3UcHt2TgBuvs0PB 2QA2p6AmTtUDn7pxgxomvjemqVk5pRdFt445pXj0tKbkDiCpC7yzkXx74SFImNye pq+8owf4tD3L70IYTCw9UUJGc7yQLdY6LyhPaRECgYAaPupxNs7B9MGtW4HCfHnz p/Q406fKsiTZ6ZC0sDK3roDlnDOzcQOxXUv/kvAR5eeG2WQoIHbcxPrvsTi5eOZO B53Ois8FR1jRXivVMXlwkVr1wz1XvfDXl6WEpG6IHULbcoU2U14Zj5JRb5Nh8HoU gggGvT061vzeGp9NpsMeig== -----END PRIVATE KEY-----','-----BEGIN PUBLIC KEY----- MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA8W+2w0Jo1oC8TG9taSqF 8t3+Ja0S2/TvTrx55QpPTQnYd0+uV5RebjQT0j2oT2xg8DK/+ucTTa99/uEI8g7q 78hQSeWuyQm/252DMAtAE0opch2e6aDqvJDaLekmdQGBCX5SRwgWm7ZAxD4uBA+Y 2nUIVZEVn/uEco7dd0n8J0k8nV0b7d2eTBIqRQU1V8kvbgUpPaPk9ed1VLlFuvaf nHrtibAiCvGyWCpkZw01/+ImJuGlyAS8bQPCzBDqzLFE6eZkitr/YFSoXZe5EbJr UHiYm4INYoxQULa/MTdJuMF7J4je4kKJMlHtvGyIwzQGEUGfyHeOdltGfuO6Wd8Y 8QIDAQAB -----END PUBLIC KEY-----');

/*!40000 ALTER TABLE `tkeys` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tuser
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tuser`;

CREATE TABLE `tuser` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logintoken` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `logintokencreationdate` datetime DEFAULT NULL,
  `creationdate` datetime NOT NULL,
  `image` char(32) DEFAULT NULL,
  `strava_athlete_id` int DEFAULT NULL,
  `strava_refresh_token` varchar(255) DEFAULT NULL,
  `strava_access_token` varchar(255) DEFAULT NULL,
  `strava_access_token_expirationdate` datetime DEFAULT NULL,
  `confirmed` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `logintoken` (`logintoken`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `tuser` WRITE;
/*!40000 ALTER TABLE `tuser` DISABLE KEYS */;

INSERT INTO `tuser` (`id`, `email`, `name`, `logintoken`, `logintokencreationdate`, `creationdate`, `image`, `strava_athlete_id`, `strava_refresh_token`, `strava_access_token`, `strava_access_token_expirationdate`, `confirmed`)
VALUES
	(6,'hans.schneider@gmail.com','haentz','0','2023-08-30 07:57:52','2023-06-27 12:22:34',NULL,2321457,'4b8f8fa7c0b43ceeb2b28bcdf475829ac395e79f','abc70bce33bd2c032268adb8a189c5205f816a42','2023-06-27 18:23:18',NULL);

/*!40000 ALTER TABLE `tuser` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
