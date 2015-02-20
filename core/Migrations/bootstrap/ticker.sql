/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `exchange` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `currency1` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency2` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_trade` decimal(24,8) DEFAULT NULL,
  `ask` decimal(24,8) DEFAULT NULL,
  `bid` decimal(24,8) DEFAULT NULL,
  `volume` decimal(24,8) DEFAULT NULL,
  `is_daily_data` tinyint(4) NOT NULL DEFAULT '0',
  `job_id` int(11) DEFAULT NULL,
  `created_at_day` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_daily_data` (`is_daily_data`),
  KEY `exchange_2` (`exchange`,`currency1`,`currency2`),
  KEY `created_at` (`created_at`),
  KEY `created_at_day` (`created_at_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
