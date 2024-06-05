-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: aqms_fs2
-- ------------------------------------------------------
-- Server version	8.2.0

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
-- Table structure for table `a_groups`
--

DROP TABLE IF EXISTS `a_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `a_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `menu_ids` text NOT NULL,
  `privileges` text NOT NULL,
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `a_groups`
--

LOCK TABLES `a_groups` WRITE;
/*!40000 ALTER TABLE `a_groups` DISABLE KEYS */;
INSERT INTO `a_groups` VALUES (1,'Administrator','1,2,3,4,5,','15,15,15,15,15,','2021-05-20 04:25:19'),(2,'Operator','1,4,5,','15,15,15,','2021-05-20 04:25:19');
/*!40000 ALTER TABLE `a_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `a_menu`
--

DROP TABLE IF EXISTS `a_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `a_menu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `seqno` int NOT NULL DEFAULT '0',
  `parent_id` int NOT NULL DEFAULT '0',
  `name_id` varchar(100) NOT NULL DEFAULT '',
  `name_en` varchar(100) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `icon` varchar(100) NOT NULL DEFAULT '',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `a_menu`
--

LOCK TABLES `a_menu` WRITE;
/*!40000 ALTER TABLE `a_menu` DISABLE KEYS */;
INSERT INTO `a_menu` VALUES (1,1,0,'Beranda','Home','/','','2021-05-20 04:25:19'),(2,2,0,'Konfigurasi','Configuration','configuration','','2021-05-20 04:25:19'),(3,3,0,'Parameter','Parameters','parameter','','2021-05-20 04:25:19'),(4,4,0,'Kalibrasi','Calibrations','calibration','','2021-05-20 04:25:19'),(5,5,0,'Ekspor','Export','export','','2021-05-20 04:25:19');
/*!40000 ALTER TABLE `a_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `a_users`
--

DROP TABLE IF EXISTS `a_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `a_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `a_users`
--

LOCK TABLES `a_users` WRITE;
/*!40000 ALTER TABLE `a_users` DISABLE KEYS */;
INSERT INTO `a_users` VALUES (1,0,'superuser@aqms','$argon2id$v=19$m=65536,t=4,p=1$aHJaWEtSSDBvTVcubFJRNQ$RwT+5/NmfXuXQNptZdAUiNemz4Cni0WZDdwXCoZz/x8','Superuser','2024-01-15 07:24:36'),(2,1,'admin@aqms','$argon2id$v=19$m=65536,t=4,p=1$aHJaWEtSSDBvTVcubFJRNQ$RwT+5/NmfXuXQNptZdAUiNemz4Cni0WZDdwXCoZz/x8','Adminstrator','2024-01-15 07:24:36'),(3,2,'operator@aqms','$argon2id$v=19$m=65536,t=4,p=1$aHJaWEtSSDBvTVcubFJRNQ$RwT+5/NmfXuXQNptZdAUiNemz4Cni0WZDdwXCoZz/x8','Operator','2024-01-15 07:24:36');
/*!40000 ALTER TABLE `a_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calibrations`
--

DROP TABLE IF EXISTS `calibrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calibrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `calibrator_name` varchar(255) NOT NULL,
  `started_at` varchar(20) NOT NULL,
  `finished_at` varchar(20) NOT NULL,
  `sensor_reader_id` int NOT NULL DEFAULT '0',
  `pin` int NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `calibrator_name` (`calibrator_name`),
  KEY `started_at` (`started_at`),
  KEY `sensor_reader_id` (`sensor_reader_id`),
  KEY `pin` (`pin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calibrations`
--

LOCK TABLES `calibrations` WRITE;
/*!40000 ALTER TABLE `calibrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `calibrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configurations`
--

DROP TABLE IF EXISTS `configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configurations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `content` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configurations`
--

LOCK TABLES `configurations` WRITE;
/*!40000 ALTER TABLE `configurations` DISABLE KEYS */;
INSERT INTO `configurations` VALUES (1,'aqms_code','AQMS_EFS2'),(2,'id_stasiun',''),(3,'nama_stasiun','AQMS MASTER'),(4,'address','-'),(5,'city','-'),(6,'province','-'),(7,'latitude','0'),(8,'longitude','0'),(9,'pump_interval','360'),(10,'pump_state','0'),(11,'pump_last','2024-02-05 20:14:32'),(12,'pump_speed','100'),(13,'selenoid_state','q'),(14,'selenoid_names',''),(15,'selenoid_commands','q;w;e;r'),(16,'purge_state','o'),(17,'data_interval','30'),(18,'graph_interval','0'),(19,'is_sampling','0'),(20,'sampler_operator_name',''),(21,'id_sampling',''),(22,'start_sampling','0'),(23,'zerocal_schedule','00:00:00'),(24,'zerocal_duration','300'),(25,'is_zerocal','0'),(26,'calibrator_name','TUT'),(27,'zerocal_started_at',''),(28,'zerocal_finished_at',''),(29,'setSpan',''),(30,'is_valve_calibrator','0'),(31,'is_cems','0'),(32,'is_valve_calibrator','0'),(33,'is_psu_restarting','1'),(34,'restart_schedule',''),(35,'last_restart_schedule',''),(36,'is_sentto_klhk','1'),(37,'klhk_api_server','ispu.menlhk.go.id'),(38,'klhk_api_username','pt_trusur_unggul_teknusa'),(39,'klhk_api_password','c6eXK8EUpbuCoaki'),(40,'klhk_api_key',''),(41,'is_sentto_trusur','1'),(42,'trusur_api_server','api.trusur.tech'),(43,'trusur_api_username','KLHK-2019'),(44,'trusur_api_password','Project2016-2019'),(45,'trusur_api_key','VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA=='),(46,'iot_path','/iot/iot/'),(47,'is_auto_restart','1'),(48,'setSpan','');
/*!40000 ALTER TABLE `configurations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formula_references`
--

DROP TABLE IF EXISTS `formula_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formula_references` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parameter_id` int NOT NULL DEFAULT '0',
  `min_value` double NOT NULL DEFAULT '0',
  `max_value` double NOT NULL DEFAULT '0',
  `formula` varchar(255) NOT NULL,
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parameter_id` (`parameter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formula_references`
--

LOCK TABLES `formula_references` WRITE;
/*!40000 ALTER TABLE `formula_references` DISABLE KEYS */;
INSERT INTO `formula_references` VALUES (1,31,-9999999999,10.650969,'(0.43899185345258 * $x) + 0.32431137762404','2023-12-08 05:41:28'),(2,31,10.65097,11.296878,'(7.5862197093084 * $x) - 75.700598537253','2023-12-08 05:41:28'),(3,31,11.296879,9999999,'(12.60501713521 * $x) - 132.29735336939','2023-12-08 05:41:28');
/*!40000 ALTER TABLE `formula_references` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ispu`
--

DROP TABLE IF EXISTS `ispu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ispu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ispu_at` datetime NOT NULL,
  `parameter_id` int NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  `ispu` int NOT NULL DEFAULT '0',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ispu_at` (`ispu_at`),
  KEY `parameter_id` (`parameter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ispu`
--

LOCK TABLES `ispu` WRITE;
/*!40000 ALTER TABLE `ispu` DISABLE KEYS */;
/*!40000 ALTER TABLE `ispu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `measurement_histories`
--

DROP TABLE IF EXISTS `measurement_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `measurement_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parameter_id` int NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  `sensor_value` double NOT NULL DEFAULT '0',
  `is_averaged` tinyint NOT NULL DEFAULT '0',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parameter_id` (`parameter_id`),
  KEY `is_averaged` (`is_averaged`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `measurement_histories`
--

LOCK TABLES `measurement_histories` WRITE;
/*!40000 ALTER TABLE `measurement_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `measurement_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `measurement_logs`
--

DROP TABLE IF EXISTS `measurement_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `measurement_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parameter_id` int NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  `sensor_value` double NOT NULL DEFAULT '0',
  `is_averaged` tinyint NOT NULL DEFAULT '0',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parameter_id` (`parameter_id`),
  KEY `is_averaged` (`is_averaged`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `measurement_logs`
--

LOCK TABLES `measurement_logs` WRITE;
/*!40000 ALTER TABLE `measurement_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `measurement_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `measurements`
--

DROP TABLE IF EXISTS `measurements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `measurements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `time_group` datetime NOT NULL,
  `parameter_id` int NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  `sensor_value` double NOT NULL DEFAULT '0',
  `ppm_value` double DEFAULT '0',
  `is_sent_cloud` tinyint NOT NULL DEFAULT '0',
  `sent_cloud_at` datetime NOT NULL,
  `is_sent_klhk` tinyint NOT NULL DEFAULT '0',
  `sent_klhk_at` datetime NOT NULL,
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `time_group_parameter_id` (`time_group`,`parameter_id`),
  KEY `is_sent_cloud` (`is_sent_cloud`),
  KEY `is_sent_klhk` (`is_sent_klhk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `measurements`
--

LOCK TABLES `measurements` WRITE;
/*!40000 ALTER TABLE `measurements` DISABLE KEYS */;
/*!40000 ALTER TABLE `measurements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2021-05-02-100939','App\\Database\\Migrations\\Configurations','default','App',1621484654,1),(2,'2021-05-02-101023','App\\Database\\Migrations\\Measurements','default','App',1621484654,1),(3,'2021-05-02-101033','App\\Database\\Migrations\\MeasurementLogs','default','App',1621484655,1),(4,'2021-05-02-101052','App\\Database\\Migrations\\Ispu','default','App',1621484655,1),(5,'2021-05-02-101105','App\\Database\\Migrations\\Parameters','default','App',1621484655,1),(6,'2021-05-02-101127','App\\Database\\Migrations\\SerialPorts','default','App',1621484655,1),(7,'2021-05-02-101151','App\\Database\\Migrations\\AGroups','default','App',1621484655,1),(8,'2021-05-02-101157','App\\Database\\Migrations\\AMenu','default','App',1621484655,1),(9,'2021-05-02-101200','App\\Database\\Migrations\\AUsers','default','App',1621484655,1),(10,'2021-05-02-101313','App\\Database\\Migrations\\SensorReaders','default','App',1621484655,1),(11,'2021-05-02-101324','App\\Database\\Migrations\\SensorValues','default','App',1621484655,1),(12,'2021-05-02-101336','App\\Database\\Migrations\\SensorValueLogs','default','App',1621484655,1),(13,'2021-05-02-131550','App\\Database\\Migrations\\MeasurementHistories','default','App',1621484656,1),(14,'2021-05-05-101829','App\\Database\\Migrations\\AlterMeasurements','default','App',1621484656,1),(15,'2021-05-05-233406','App\\Database\\Migrations\\AlterMeasurements20210506','default','App',1621484656,1),(16,'2021-11-15-115849','App\\Database\\Migrations\\Calibrations','default','App',1637115377,2),(17,'2021-11-18-025803','App\\Database\\Migrations\\AlterCalibration20211118','default','App',1638330437,3),(18,'2022-04-06-071743','App\\Database\\Migrations\\AlterSerialPorts','default','App',1702014058,4),(19,'2022-04-13-010212','App\\Database\\Migrations\\InsertConfiguration20220413','default','App',1702014058,4),(20,'2022-04-13-014124','App\\Database\\Migrations\\InsertNewParameters20220413','default','App',1702014058,4),(21,'2022-05-23-010213','App\\Database\\Migrations\\IsValveCalibrator','default','App',1702014058,4),(22,'2022-05-23-091431','App\\Database\\Migrations\\IsPsuRestarting','default','App',1702014058,4),(23,'2022-05-24-101126','App\\Database\\Migrations\\RestartSchedule','default','App',1702014058,4),(24,'2022-05-30-011414','App\\Database\\Migrations\\ConfigurationsServers','default','App',1702014058,4),(25,'2022-08-19-081016','App\\Database\\Migrations\\FormulaReferences','default','App',1702014088,5),(26,'2022-11-08-042014','App\\Database\\Migrations\\IsAutoRestart','default','App',1702014088,5),(27,'2023-01-25-064941','App\\Database\\Migrations\\Motherboard','default','App',1702014088,5),(28,'2023-12-08-053805','App\\Database\\Migrations\\CreateTableRealtimeValues','default','App',1702014159,6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motherboard`
--

DROP TABLE IF EXISTS `motherboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motherboard` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sensorname` varchar(50) NOT NULL,
  `is_enable` tinyint NOT NULL DEFAULT '0',
  `is_priority` tinyint NOT NULL DEFAULT '0',
  `command` varchar(255) NOT NULL,
  `prefix_return` varchar(255) NOT NULL,
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_enable` (`is_enable`),
  KEY `is_priority` (`is_priority`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motherboard`
--

LOCK TABLES `motherboard` WRITE;
/*!40000 ALTER TABLE `motherboard` DISABLE KEYS */;
INSERT INTO `motherboard` VALUES (1,'MEMBRASENS PPM',1,1,'data.membrasens.ppm','END_MEMBRASENS_PPM','2023-12-08 05:41:28'),(2,'MEMBRASENS TEMP',1,0,'data.membrasens.temp','END_MEMBRASENS_TEMP','2023-12-08 05:41:28'),(3,'SEMEATECH',1,1,'data.semeatech.5','SEMEATECH FINISH;','2023-12-08 05:41:28'),(4,'METONE 1',1,1,'data.pm.1','END_PM1','2023-12-08 05:41:28'),(5,'METONE 2',1,1,'data.pm.2','END_PM2','2023-12-08 05:41:28'),(6,'VOLTAGE CURRENT',1,0,'data.ina219','END_INA219','2023-12-08 05:41:28'),(7,'PRESSURE BME',1,0,'data.bme','END_BME','2023-12-08 05:41:28'),(8,'PRESSURE',1,0,'data.pressure','END_PRESSURE','2023-12-08 05:41:28'),(9,'PUMP',1,0,'data.pump','END_PUMP','2023-12-08 05:41:28'),(10,'SENTEC',0,0,'data.sentec','END_SENTEC','2023-12-08 05:41:28');
/*!40000 ALTER TABLE `motherboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parameters`
--

DROP TABLE IF EXISTS `parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parameters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `caption_id` varchar(100) NOT NULL,
  `caption_en` varchar(100) NOT NULL,
  `default_unit` varchar(10) NOT NULL,
  `molecular_mass` double NOT NULL DEFAULT '0',
  `formula` varchar(255) NOT NULL,
  `is_view` tinyint NOT NULL DEFAULT '0',
  `p_type` varchar(30) NOT NULL DEFAULT 'gas',
  `is_graph` tinyint NOT NULL DEFAULT '0',
  `sensor_value_id` int NOT NULL DEFAULT '0',
  `voltage1` double NOT NULL DEFAULT '0',
  `voltage2` double NOT NULL DEFAULT '0',
  `concentration1` double NOT NULL DEFAULT '0',
  `concentration2` double NOT NULL DEFAULT '0',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parameters`
--

LOCK TABLES `parameters` WRITE;
/*!40000 ALTER TABLE `parameters` DISABLE KEYS */;
INSERT INTO `parameters` VALUES (1,'no2','NO<sub>2</sub>','NO<sub>2</sub>','µg/m<sup>3',46.01,'round(explode(\";\",$sensor[1][0])[3] * 46010 / 24.45,0)',1,'gas',1,1,0,0,0,0,'2023-12-01 06:47:38'),(2,'o3','O<sub>3</sub>','O<sub>3</sub>','µg/m<sup>3',48,'round(explode(\";\",$sensor[1][0])[4] * 48000 / 24.45,0)',1,'gas',1,1,0,0,0,0,'2023-12-01 06:47:38'),(3,'co','CO','CO','µg/m<sup>3',28.01,'round(explode(\";\",$sensor[2][0])[3] * 28010 / 24.45,0)',1,'gas',1,2,0,0,0,0,'2023-12-01 06:47:38'),(4,'so2','SO<sub>2</sub>','SO<sub>2</sub>','µg/m<sup>3',64.06,'round(explode(\";\",$sensor[1][0])[1] * 64060 / 24.45,0)',1,'gas',1,1,0,0,0,0,'2023-12-01 06:47:38'),(5,'hc','HC','HC','µg/m<sup>3',78.9516,'round(explode(\";\",$sensor[2][0])[4] * 789.516 / 24.45,0)',1,'gas',1,2,0.08978947682848,0.08978957682848,0,2,'2023-12-01 06:47:38'),(6,'h2s','H<sub>2</sub>S','H<sub>2</sub>S','µg/m<sup>3',34.08,'',0,'gas',0,0,0,0,0,0,'2021-05-20 04:25:20'),(7,'cs2','CS<sub>2</sub>','CS<sub>2</sub>','µg/m<sup>3',76.1407,'',0,'gas',0,0,0,0,0,0,'2021-05-20 04:25:20'),(8,'nh3','NH<sub>3</sub>','NH<sub>3</sub>','µg/m<sup>3',76.1407,'',0,'gas',0,0,0,0,0,0,'2021-05-20 04:25:20'),(9,'ch4','CH<sub>4</sub>','CH<sub>4</sub>','µg/m<sup>3',16.04,'',0,'gas',0,0,0,0,0,0,'2021-05-20 04:25:20'),(10,'voc','VOC','VOC','µg/m<sup>3',78.9516,'',0,'gas',0,0,0,0,0,0,'2021-05-20 04:25:20'),(11,'nmhc','NMHC','NMHC','µg/m<sup>3',110,'',0,'gas',0,0,0,0,0,0,'2021-05-20 04:25:20'),(12,'pm25','PM2.5','PM2.5','µg/m<sup>3',0,'substr($sensor[9][0],2,7) * 1000',1,'particulate',1,0,0,0,0,0,'2023-12-01 06:46:53'),(13,'pm25_flow','PM2.5 Flow','PM2.5 Flow','l/mnt',0,'substr($sensor[9][0],10,3)',1,'particulate_flow',1,0,0,0,0,0,'2023-12-01 06:46:53'),(14,'pm10','PM10','PM10','µg/m<sup>3',0,'substr($sensor[10][0],2,7) * 1000',1,'particulate',1,0,0,0,0,0,'2023-12-01 06:46:53'),(15,'pm10_flow','PM10 Flow','PM10 Flow','l/mnt',0,'substr($sensor[10][0],10,3)',1,'particulate_flow',1,0,0,0,0,0,'2023-12-01 06:46:53'),(16,'tsp','TSP','TSP','µg/m<sup>3',0,'',0,'particulate',0,0,0,0,0,0,'2021-05-20 04:25:20'),(17,'tsp_flow','TSP Flow','TSP Flow','l/mnt',0,'',0,'particulate_flow',0,0,0,0,0,0,'2021-05-20 04:25:20'),(18,'pressure','Tekanan','Barometer','MBar',0,'round((explode(\";\",$sensor[6][0])[2] * 33.8639),2)',1,'weather',0,0,0,0,0,0,'2021-05-31 03:57:06'),(19,'wd','Arah angin','Wind Direction','°',0,'explode(\";\",$sensor[6][0])[8]',1,'weather',0,0,0,0,0,0,'2021-05-31 03:57:29'),(20,'ws','Kec. Angin','Wind Speed','Km/h',0,'explode(\";\",$sensor[6][0])[6]',1,'weather',0,0,0,0,0,0,'2021-05-31 03:57:42'),(21,'temperature','Suhu','Temperature','°C',0,'round((explode(\";\",$sensor[6][0])[5] - 32) * 5/9,1)',1,'weather',0,0,0,0,0,0,'2021-09-16 05:40:45'),(22,'humidity','Kelembaban','Humidity','%',0,'explode(\";\",$sensor[6][0])[9]',1,'weather',0,0,0,0,0,0,'2021-05-31 03:58:07'),(23,'sr','Solar Radiasi','Solar Radiation','watt/m2',0,'explode(\";\",$sensor[6][0])[12]',1,'weather',0,0,0,0,0,0,'2021-05-31 03:58:18'),(24,'rain_intensity','Curah Hujan','Rain Rate','mm/h',0,'explode(\";\",$sensor[6][0])[15]',1,'weather',0,0,0,0,0,0,'2021-05-31 03:58:33'),(25,'pm10_bar','Tekanan','Barometer','MBar',0,'',0,'weather',0,0,0,0,0,0,'2021-05-20 04:25:20'),(26,'pm10_humid','Kelembaban','Humidity','%',0,'',0,'weather',0,0,0,0,0,0,'2021-05-20 04:25:20'),(27,'pm10_temp','Suhu','Temperature','°C',0,'',0,'weather',0,0,0,0,0,0,'2021-05-20 04:25:20'),(28,'pm25_bar','Tekanan','Barometer','MBar',0,'',0,'weather',0,0,0,0,0,0,'2021-05-20 04:25:20'),(29,'pm25_humid','Kelembaban','Humidity','%',0,'',0,'weather',0,0,0,0,0,0,'2021-05-20 04:25:20'),(30,'pm25_temp','Suhu','Temperature','°C',0,'',0,'weather',0,0,0,0,0,0,'2021-05-20 04:25:20'),(31,'co2','CO<sub>2</sub>','CO<sub>2</sub>','µg/m<sup>3',44.01,'round((explode(\";\",$sensor[9][0])[0]) * 44010 / 24.45,3)',0,'gas',1,0,0,0,0,0,'2023-12-08 05:56:15'),(32,'o2','O<sub>2</sub>','O<sub>2</sub>','µg/m<sup>3',15.99,'round((explode(\";\",$sensor[10][0])[0]) * 15990 / 24.45,3)',0,'gas',1,0,0,0,0,0,'2023-12-08 06:15:08'),(33,'no','NO','NO','µg/m<sup>3',30.0061,'round((explode(\";\",$sensor[10][0])[0]) * 30006.1 / 24.45,3)',0,'gas',1,0,0,0,0,0,'2023-12-08 05:56:15');
/*!40000 ALTER TABLE `parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realtime_values`
--

DROP TABLE IF EXISTS `realtime_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realtime_values` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parameter_id` int DEFAULT NULL,
  `measured` double DEFAULT '0',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realtime_values`
--

LOCK TABLES `realtime_values` WRITE;
/*!40000 ALTER TABLE `realtime_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `realtime_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_readers`
--

DROP TABLE IF EXISTS `sensor_readers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sensor_readers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `driver` varchar(50) NOT NULL,
  `sensor_code` varchar(30) NOT NULL,
  `baud_rate` varchar(100) NOT NULL,
  `pins` varchar(200) NOT NULL,
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_readers`
--

LOCK TABLES `sensor_readers` WRITE;
/*!40000 ALTER TABLE `sensor_readers` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_readers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_value_logs`
--

DROP TABLE IF EXISTS `sensor_value_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sensor_value_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sensor_value_id` int NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL DEFAULT '',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sensor_value_id` (`sensor_value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_value_logs`
--

LOCK TABLES `sensor_value_logs` WRITE;
/*!40000 ALTER TABLE `sensor_value_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_value_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_values`
--

DROP TABLE IF EXISTS `sensor_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sensor_values` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sensor_reader_id` int NOT NULL DEFAULT '0',
  `pin` int NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sensor_reader_id` (`sensor_reader_id`),
  KEY `pin` (`pin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_values`
--

LOCK TABLES `sensor_values` WRITE;
/*!40000 ALTER TABLE `sensor_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `serial_ports`
--

DROP TABLE IF EXISTS `serial_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `serial_ports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `port` varchar(20) NOT NULL,
  `id_product` varchar(100) NOT NULL,
  `id_vendor` varchar(100) NOT NULL,
  `serial` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `is_used` tinyint NOT NULL DEFAULT '0',
  `xtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `port` (`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `serial_ports`
--

LOCK TABLES `serial_ports` WRITE;
/*!40000 ALTER TABLE `serial_ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `serial_ports` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-02-15  9:33:11
