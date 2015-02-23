/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summary_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `summary_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `is_recent` tinyint(4) NOT NULL DEFAULT '0',
  `balance` decimal(24,8) DEFAULT NULL,
  `is_daily_data` tinyint(4) NOT NULL DEFAULT '0',
  `job_id` int(11) DEFAULT NULL,
  `created_at_day` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `summary_type` (`summary_type`),
  KEY `user_id` (`user_id`),
  KEY `is_recent` (`is_recent`),
  KEY `is_daily_data` (`is_daily_data`),
  KEY `created_at_day` (`created_at_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
