/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_generic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_queue` timestamp NULL DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `failures` tinyint(4) NOT NULL DEFAULT '0',
  `first_failure` timestamp NULL DEFAULT NULL,
  `multiplier` decimal(24,8) NOT NULL DEFAULT '1.00000000',
  `is_disabled_manually` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `currency` (`currency`),
  KEY `last_queue` (`last_queue`),
  KEY `is_disabled` (`is_disabled`),
  KEY `is_disabled_manually` (`is_disabled_manually`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
