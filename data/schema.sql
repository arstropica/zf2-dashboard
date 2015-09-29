-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 29, 2015 at 02:51 PM
-- Server version: 5.5.42-cll
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `zf2_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Table structure for table `account_leads`
--

CREATE TABLE IF NOT EXISTS `account_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `assigned` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_211F9C2355458D` (`lead_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Table structure for table `api`
--

CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `api_accounts`
--

CREATE TABLE IF NOT EXISTS `api_accounts` (
  `api_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  PRIMARY KEY (`api_id`,`account_id`),
  KEY `IDX_19F3542354963938` (`api_id`),
  KEY `IDX_19F354239B6B5FBA` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_options`
--

CREATE TABLE IF NOT EXISTS `api_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL,
  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scope` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci,
  `label` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3D0B8EE054963938` (`api_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `api_settings`
--

CREATE TABLE IF NOT EXISTS `api_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `api_id` int(11) NOT NULL,
  `api_setting` int(11) NOT NULL,
  `api_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_367E6A4A9B6B5FBA` (`account_id`),
  KEY `IDX_367E6A4A54963938` (`api_id`),
  KEY `IDX_367E6A4A724ACCFF` (`api_setting`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `occurred` datetime NOT NULL,
  `message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=648 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_account`
--

CREATE TABLE IF NOT EXISTS `events_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2B572FBE9B6B5FBA` (`account_id`),
  KEY `IDX_2B572FBE71F7E88B` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=448 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_api`
--

CREATE TABLE IF NOT EXISTS `events_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FB944F5B54963938` (`api_id`),
  KEY `IDX_FB944F5B71F7E88B` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=141 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_api_account`
--

CREATE TABLE IF NOT EXISTS `events_api_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `api_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A6268C09B6B5FBA` (`account_id`),
  KEY `IDX_A6268C054963938` (`api_id`),
  KEY `IDX_A6268C071F7E88B` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_api_email`
--

CREATE TABLE IF NOT EXISTS `events_api_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `address_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `outcome` tinyint(1) NOT NULL,
  `response` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_5AF34D869B6B5FBA` (`account_id`),
  KEY `IDX_5AF34D8671F7E88B` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=85 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_api_tenstreet`
--

CREATE TABLE IF NOT EXISTS `events_api_tenstreet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `outcome` tinyint(1) NOT NULL,
  `response` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_8918A9379B6B5FBA` (`account_id`),
  KEY `IDX_8918A93771F7E88B` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=210 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_error`
--

CREATE TABLE IF NOT EXISTS `events_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `trace` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_ED61745771F7E88B` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=105 ;

-- --------------------------------------------------------

--
-- Table structure for table `events_lead`
--

CREATE TABLE IF NOT EXISTS `events_lead` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_44C165B155458D` (`lead_id`),
  KEY `IDX_44C165B171F7E88B` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1125 ;

-- --------------------------------------------------------

--
-- Table structure for table `lead`
--

CREATE TABLE IF NOT EXISTS `lead` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timecreated` datetime NOT NULL,
  `referrer` varchar(255) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `IDX_289161CB9B6B5FBA` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=579 ;

-- --------------------------------------------------------

--
-- Table structure for table `lead_attributes`
--

CREATE TABLE IF NOT EXISTS `lead_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(255) NOT NULL,
  `attribute_desc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=154 ;

-- --------------------------------------------------------

--
-- Table structure for table `lead_attribute_values`
--

CREATE TABLE IF NOT EXISTS `lead_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) DEFAULT NULL,
  `attribute_id` int(11) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_attribute_id` (`attribute_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5154 ;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE IF NOT EXISTS `oauth_access_tokens` (
  `access_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_authorization_codes`
--

CREATE TABLE IF NOT EXISTS `oauth_authorization_codes` (
  `authorization_code` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `redirect_uri` varchar(2000) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  `id_token` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`authorization_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `client_id` varchar(80) NOT NULL,
  `client_secret` varchar(80) DEFAULT NULL,
  `redirect_uri` varchar(2000) NOT NULL,
  `grant_types` varchar(80) DEFAULT NULL,
  `scope` varchar(2000) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_jwt`
--

CREATE TABLE IF NOT EXISTS `oauth_jwt` (
  `client_id` varchar(80) NOT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `public_key` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE IF NOT EXISTS `oauth_refresh_tokens` (
  `refresh_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`refresh_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_scopes`
--

CREATE TABLE IF NOT EXISTS `oauth_scopes` (
  `type` varchar(255) NOT NULL DEFAULT 'supported',
  `scope` varchar(2000) DEFAULT NULL,
  `client_id` varchar(80) DEFAULT NULL,
  `is_default` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_users`
--

CREATE TABLE IF NOT EXISTS `oauth_users` (
  `username` varchar(255) NOT NULL,
  `password` varchar(2000) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `roleId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_57698A6AB8C2FD88` (`roleId`),
  KEY `IDX_57698A6A727ACA70` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `displayName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `apiKey` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_account_linker`
--

CREATE TABLE IF NOT EXISTS `user_account_linker` (
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`account_id`),
  KEY `IDX_2F9D4197A76ED395` (`user_id`),
  KEY `IDX_2F9D41979B6B5FBA` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_role_linker`
--

CREATE TABLE IF NOT EXISTS `user_role_linker` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_61117899A76ED395` (`user_id`),
  KEY `IDX_61117899D60322AC` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_leads`
--
ALTER TABLE `account_leads`
  ADD CONSTRAINT `FK_211F9C2355458D` FOREIGN KEY (`lead_id`) REFERENCES `lead` (`id`);

--
-- Constraints for table `api_accounts`
--
ALTER TABLE `api_accounts`
  ADD CONSTRAINT `FK_19F3542354963938` FOREIGN KEY (`api_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `FK_19F354239B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `api` (`id`);

--
-- Constraints for table `api_options`
--
ALTER TABLE `api_options`
  ADD CONSTRAINT `FK_3D0B8EE054963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`);

--
-- Constraints for table `api_settings`
--
ALTER TABLE `api_settings`
  ADD CONSTRAINT `FK_367E6A4A54963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  ADD CONSTRAINT `FK_367E6A4A724ACCFF` FOREIGN KEY (`api_setting`) REFERENCES `api_options` (`id`),
  ADD CONSTRAINT `FK_367E6A4A9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `events_account`
--
ALTER TABLE `events_account`
  ADD CONSTRAINT `FK_2B572FBE71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `FK_2B572FBE9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `events_api`
--
ALTER TABLE `events_api`
  ADD CONSTRAINT `FK_FB944F5B54963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  ADD CONSTRAINT `FK_FB944F5B71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `events_api_account`
--
ALTER TABLE `events_api_account`
  ADD CONSTRAINT `FK_A6268C054963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  ADD CONSTRAINT `FK_A6268C071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `FK_A6268C09B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `events_api_email`
--
ALTER TABLE `events_api_email`
  ADD CONSTRAINT `FK_5AF34D8671F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `FK_5AF34D869B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `events_api_tenstreet`
--
ALTER TABLE `events_api_tenstreet`
  ADD CONSTRAINT `FK_8918A93771F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `FK_8918A9379B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `events_error`
--
ALTER TABLE `events_error`
  ADD CONSTRAINT `FK_ED61745771F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `events_lead`
--
ALTER TABLE `events_lead`
  ADD CONSTRAINT `FK_44C165B155458D` FOREIGN KEY (`lead_id`) REFERENCES `lead` (`id`),
  ADD CONSTRAINT `FK_44C165B171F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `lead`
--
ALTER TABLE `lead`
  ADD CONSTRAINT `FK_289161CB9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `lead_attribute_values`
--
ALTER TABLE `lead_attribute_values`
  ADD CONSTRAINT `FK_39CD63EA55458D` FOREIGN KEY (`lead_id`) REFERENCES `lead` (`id`),
  ADD CONSTRAINT `FK_39CD63EAB6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `lead_attributes` (`id`);

--
-- Constraints for table `role`
--
ALTER TABLE `role`
  ADD CONSTRAINT `FK_57698A6A727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `role` (`id`);

--
-- Constraints for table `user_account_linker`
--
ALTER TABLE `user_account_linker`
  ADD CONSTRAINT `FK_2F9D41979B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `FK_2F9D4197A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user_role_linker`
--
ALTER TABLE `user_role_linker`
  ADD CONSTRAINT `FK_61117899D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`),
  ADD CONSTRAINT `FK_61117899A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
