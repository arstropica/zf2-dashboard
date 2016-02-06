CREATE DATABASE  IF NOT EXISTS `zf2_db` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `zf2_db`;
-- MySQL dump 10.13  Distrib 5.6.17, for Win64 (x86_64)
--
-- Host: localhost    Database: zf2_db
-- ------------------------------------------------------
-- Server version	5.5.44-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `agent`
--

DROP TABLE IF EXISTS `agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) DEFAULT NULL,
  `updated` datetime NOT NULL,
  `scope` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `orphan` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_268B9C9D4BD2A4C0` (`report_id`),
  KEY `IDX_268B9C9D9B6B5FBA` (`account_id`),
  CONSTRAINT `FK_268B9C9D9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `FK_268B9C9D4BD2A4C0` FOREIGN KEY (`report_id`) REFERENCES `report` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `agent_criteria`
--

DROP TABLE IF EXISTS `agent_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agent_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `relationship_id` int(11) NOT NULL,
  `weight` double DEFAULT NULL,
  `required` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_80CE8215B6E62EFA` (`attribute_id`),
  KEY `IDX_80CE82153414710B` (`agent_id`),
  KEY `IDX_80CE82152C41D668` (`relationship_id`),
  CONSTRAINT `FK_80CE82152C41D668` FOREIGN KEY (`relationship_id`) REFERENCES `agent_criterion_relationship` (`id`),
  CONSTRAINT `FK_80CE82153414710B` FOREIGN KEY (`agent_id`) REFERENCES `agent` (`id`),
  CONSTRAINT `FK_80CE8215B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `lead_attributes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `agent_criterion_relationship`
--

DROP TABLE IF EXISTS `agent_criterion_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agent_criterion_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `symbol` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `allowed` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `input` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_criterion_relationship`
--

LOCK TABLES `agent_criterion_relationship` WRITE;
/*!40000 ALTER TABLE `agent_criterion_relationship` DISABLE KEYS */;
INSERT INTO `agent_criterion_relationship` VALUES (1,'Boolean','boolean','true/false','a:1:{i:0;s:7:\"boolean\";}','boolean'),(2,'Contains','contains','=*','a:2:{i:0;s:6:\"string\";i:1;s:4:\"text\";}','string'),(3,'Equality','equality','=','a:3:{i:0;s:6:\"number\";i:1;s:6:\"string\";i:2;s:4:\"text\";}','string'),(4,'InEquality','inequality','!=','a:3:{i:0;s:6:\"number\";i:1;s:6:\"string\";i:2;s:4:\"text\";}','string'),(5,'Greater','greater','>','a:2:{i:0;s:6:\"number\";i:1;s:4:\"date\";}','string'),(6,'Less','less','<','a:2:{i:0;s:6:\"number\";i:1;s:4:\"date\";}','string'),(7,'Range','range','<>','a:1:{i:0;s:6:\"number\";}','range'),(8,'Multiple','multiple','[ ]','a:1:{i:0;s:8:\"multiple\";}','multiple'),(9,'DateRange','daterange','<>','a:1:{i:0;s:4:\"date\";}','daterange'),(10,'Location','location','locale','a:1:{i:0;s:8:\"location\";}','location');
/*!40000 ALTER TABLE `agent_criterion_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agent_criterion_values`
--

DROP TABLE IF EXISTS `agent_criterion_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agent_criterion_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `criterion_id` int(11) DEFAULT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  `string` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `boolean` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `daterange` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `multiple` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `range` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_87AFC9AB97766307` (`criterion_id`),
  KEY `IDX_87AFC9AB727ACA70` (`parent_id`),
  CONSTRAINT `FK_87AFC9AB727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `agent_criterion_values` (`id`),
  CONSTRAINT `FK_87AFC9AB97766307` FOREIGN KEY (`criterion_id`) REFERENCES `agent_criteria` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api`
--

DROP TABLE IF EXISTS `api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api`
--

LOCK TABLES `api` WRITE;
/*!40000 ALTER TABLE `api` DISABLE KEYS */;
INSERT INTO `api` VALUES (1,'Tenstreet','Tenstreet provides a robust api, which allows the client to send and receive data to Tenstreet. The Tenstreet API supports both a standard HTTP \'POST\' and a Simple Object Access Protocol (SOAP) when receiving data from the client.'),(2,'Email','Send data to an email address.'),(3,'WebWorks','Send data via POST to the WebWorks Service');
/*!40000 ALTER TABLE `api` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `api_accounts`
--

DROP TABLE IF EXISTS `api_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_accounts` (
  `api_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  PRIMARY KEY (`api_id`,`account_id`),
  KEY `IDX_19F3542354963938` (`api_id`),
  KEY `IDX_19F354239B6B5FBA` (`account_id`),
  CONSTRAINT `FK_19F3542354963938` FOREIGN KEY (`api_id`) REFERENCES `account` (`id`),
  CONSTRAINT `FK_19F354239B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `api` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_options`
--

DROP TABLE IF EXISTS `api_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL,
  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scope` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci,
  `label` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3D0B8EE054963938` (`api_id`),
  CONSTRAINT `FK_3D0B8EE054963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_options`
--

LOCK TABLES `api_options` WRITE;
/*!40000 ALTER TABLE `api_options` DISABLE KEYS */;
INSERT INTO `api_options` VALUES (1,1,'Source','TMPLead','global','Provided by Tenstreet. Indicates the source of the application, which is used in many places in Dashboard and is how the customer will see you. If you are a job board, you will have a unique Source that identifies you. In some rare cases, you may have more than one Source (a company who has more than one business or job board, for example, might have a Source for each unique job board).','Source (Tenstreet)'),(2,1,'ClientId','81','global','Provided by Tenstreet. The ClientId identifies the sender uniquely and is completely unrelated to the CompanyId(s) you are sending your information to.','Client Id (Tenstreet)'),(3,1,'Password','o$rgM7j$h70Q#b*4N4$#q36re','global','Provided by Tenstreet. This is a password generated specifically for authenticating to the Tenstreet API. It will be used for all API services, but nowhere else in the system.','Password (Tenstreet)'),(4,1,'Mode','DEV','global','DEV or PROD. Informational only, confirms the system that the data came from.','Mode (Tenstreet)'),(5,1,'Service','subject_upload','global','The service you are requesting. Tenstreet will provide the appropriate value for this field. For sending application data to Tenstreet, this value is: subject_upload','Service (Tenstreet)'),(6,1,'CompanyId','','local','Provided by Tenstreet. Your internal Company ID, used to route your info to the correct Tenstreet customer','Company Id (Tenstreet)'),(7,1,'Company','','local','Name of the Company the data came from. Informational Only and helpful for troubleshooting, especially when the CompanyId provided is incorrect.','Company Name (Tenstreet)'),(8,2,'address_from','hello@arstropica.com','global','The global sender address (and reply address) for the email.','From Address (Email)'),(9,2,'subject','New Lead from TMP','global','The global email subject line.','Subject (Email)'),(10,2,'address_from','','local','The sender address (and reply address) for the email.','From Address (Email)'),(11,2,'subject','','local','The email subject line.','Subject (Email)'),(12,2,'address_to','','local','The email recipient.','To Address (Email)'),(13,3,'Source','600','global','Provided by WebWorks. Indicates the source of the application, which is used in many places in Dashboard and is how the customer will see you. If you are a job board, you will have a unique Source that identifies you. In some rare cases, you may have more than one Source (a company who has more than one business or job board, for example, might have a Source for each unique job board).','Source (WebWorks)'),(14,3,'CompanyId','','local','Provided by WebWorks. Your internal Company ID, used to route your info to the correct WebWorks customer','Company Id (WebWorks)'),(15,3,'CompanyName','','local','Name of the Company the data came from. Informational Only and helpful for troubleshooting, especially when the CompanyId provided is incorrect.','Company Name (WebWorks)');
/*!40000 ALTER TABLE `api_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_settings`
--

DROP TABLE IF EXISTS `api_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `api_id` int(11) NOT NULL,
  `api_setting` int(11) NOT NULL,
  `api_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_367E6A4A9B6B5FBA` (`account_id`),
  KEY `IDX_367E6A4A54963938` (`api_id`),
  KEY `IDX_367E6A4A724ACCFF` (`api_setting`),
  CONSTRAINT `FK_367E6A4A54963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  CONSTRAINT `FK_367E6A4A724ACCFF` FOREIGN KEY (`api_setting`) REFERENCES `api_options` (`id`),
  CONSTRAINT `FK_367E6A4A9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `occurred` datetime NOT NULL,
  `message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2650 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_account`
--

DROP TABLE IF EXISTS `events_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2B572FBE9B6B5FBA` (`account_id`),
  KEY `IDX_2B572FBE71F7E88B` (`event_id`),
  CONSTRAINT `FK_2B572FBE71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_2B572FBE9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1294 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_agent`
--

DROP TABLE IF EXISTS `events_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_963754BB3414710B` (`agent_id`),
  KEY `IDX_963754BB71F7E88B` (`event_id`),
  CONSTRAINT `FK_963754BB3414710B` FOREIGN KEY (`agent_id`) REFERENCES `agent` (`id`),
  CONSTRAINT `FK_963754BB71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_api`
--

DROP TABLE IF EXISTS `events_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FB944F5B54963938` (`api_id`),
  KEY `IDX_FB944F5B71F7E88B` (`event_id`),
  CONSTRAINT `FK_FB944F5B54963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  CONSTRAINT `FK_FB944F5B71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_api_account`
--

DROP TABLE IF EXISTS `events_api_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_api_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `api_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A6268C09B6B5FBA` (`account_id`),
  KEY `IDX_A6268C054963938` (`api_id`),
  KEY `IDX_A6268C071F7E88B` (`event_id`),
  CONSTRAINT `FK_A6268C054963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  CONSTRAINT `FK_A6268C071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_A6268C09B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_api_email`
--

DROP TABLE IF EXISTS `events_api_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_api_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `address_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `outcome` tinyint(1) NOT NULL,
  `response` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_5AF34D869B6B5FBA` (`account_id`),
  KEY `IDX_5AF34D8671F7E88B` (`event_id`),
  CONSTRAINT `FK_5AF34D8671F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_5AF34D869B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_api_tenstreet`
--

DROP TABLE IF EXISTS `events_api_tenstreet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_api_tenstreet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `outcome` tinyint(1) NOT NULL,
  `response` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_8918A9379B6B5FBA` (`account_id`),
  KEY `IDX_8918A93771F7E88B` (`event_id`),
  CONSTRAINT `FK_8918A93771F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_8918A9379B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_api_webworks`
--

DROP TABLE IF EXISTS `events_api_webworks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_api_webworks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `outcome` tinyint(1) NOT NULL,
  `response` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_256A8ED29B6B5FBA` (`account_id`),
  KEY `IDX_256A8ED271F7E88B` (`event_id`),
  CONSTRAINT `FK_256A8ED271F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_256A8ED29B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_error`
--

DROP TABLE IF EXISTS `events_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `trace` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_ED61745771F7E88B` (`event_id`),
  CONSTRAINT `FK_ED61745771F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_lead`
--

DROP TABLE IF EXISTS `events_lead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_lead` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_44C165B155458D` (`lead_id`),
  KEY `IDX_44C165B171F7E88B` (`event_id`),
  CONSTRAINT `FK_44C165B155458D` FOREIGN KEY (`lead_id`) REFERENCES `lead` (`id`),
  CONSTRAINT `FK_44C165B171F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2727 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_report`
--

DROP TABLE IF EXISTS `events_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_16924EB14BD2A4C0` (`report_id`),
  KEY `IDX_16924EB171F7E88B` (`event_id`),
  CONSTRAINT `FK_16924EB14BD2A4C0` FOREIGN KEY (`report_id`) REFERENCES `report` (`id`),
  CONSTRAINT `FK_16924EB171F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lead`
--

DROP TABLE IF EXISTS `lead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timecreated` datetime NOT NULL,
  `referrer` varchar(255) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `lastsubmitted` datetime DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `IDX_289161CB9B6B5FBA` (`account_id`),
  CONSTRAINT `FK_289161CB9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1288 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lead_attribute_values`
--

DROP TABLE IF EXISTS `lead_attribute_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) DEFAULT NULL,
  `attribute_id` int(11) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_attribute_id` (`attribute_id`),
  CONSTRAINT `FK_39CD63EA55458D` FOREIGN KEY (`lead_id`) REFERENCES `lead` (`id`),
  CONSTRAINT `FK_39CD63EAB6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `lead_attributes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21039 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lead_attributes`
--

DROP TABLE IF EXISTS `lead_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(255) NOT NULL,
  `attribute_desc` text NOT NULL,
  `attribute_order` int(11) DEFAULT NULL,
  `attribute_type` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=615 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_access_tokens`
--

DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_access_tokens` (
  `access_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_authorization_codes`
--

DROP TABLE IF EXISTS `oauth_authorization_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_authorization_codes` (
  `authorization_code` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `redirect_uri` varchar(2000) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  `id_token` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`authorization_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_clients`
--

DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_clients` (
  `client_id` varchar(80) NOT NULL,
  `client_secret` varchar(80) DEFAULT NULL,
  `redirect_uri` varchar(2000) NOT NULL,
  `grant_types` varchar(80) DEFAULT NULL,
  `scope` varchar(2000) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_jwt`
--

DROP TABLE IF EXISTS `oauth_jwt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_jwt` (
  `client_id` varchar(80) NOT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `public_key` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_refresh_tokens` (
  `refresh_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`refresh_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_scopes`
--

DROP TABLE IF EXISTS `oauth_scopes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_scopes` (
  `type` varchar(255) NOT NULL DEFAULT 'supported',
  `scope` varchar(2000) DEFAULT NULL,
  `client_id` varchar(80) DEFAULT NULL,
  `is_default` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_users`
--

DROP TABLE IF EXISTS `oauth_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_users` (
  `username` varchar(255) NOT NULL,
  `password` varchar(2000) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C42F77843414710B` (`agent_id`),
  KEY `IDX_C42F77849B6B5FBA` (`account_id`),
  CONSTRAINT `FK_C42F77843414710B` FOREIGN KEY (`agent_id`) REFERENCES `agent` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C42F77849B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `roleId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_57698A6AB8C2FD88` (`roleId`),
  KEY `IDX_57698A6A727ACA70` (`parent_id`),
  CONSTRAINT `FK_57698A6A727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `displayName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `apiKey` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_account_linker`
--

DROP TABLE IF EXISTS `user_account_linker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_account_linker` (
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`account_id`),
  KEY `IDX_2F9D4197A76ED395` (`user_id`),
  KEY `IDX_2F9D41979B6B5FBA` (`account_id`),
  CONSTRAINT `FK_2F9D41979B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `FK_2F9D4197A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_role_linker`
--

DROP TABLE IF EXISTS `user_role_linker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role_linker` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_61117899A76ED395` (`user_id`),
  KEY `IDX_61117899D60322AC` (`role_id`),
  CONSTRAINT `FK_61117899A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_61117899D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'zf2_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-02-06 11:44:06
