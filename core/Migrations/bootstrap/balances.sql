/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `exchange` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `account_id` int(11) NOT NULL,
  `balance` decimal(24,8) NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `is_recent` tinyint(4) NOT NULL DEFAULT '0',
  `is_daily_data` tinyint(4) NOT NULL DEFAULT '0',
  `job_id` int(11) DEFAULT NULL,
  `created_at_day` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `exchange` (`exchange`),
  KEY `currency` (`currency`),
  KEY `is_recent` (`is_recent`),
  KEY `account_id` (`account_id`),
  KEY `is_daily_data` (`is_daily_data`),
  KEY `user_id_2` (`user_id`,`account_id`,`exchange`,`is_recent`),
  KEY `created_at_day` (`created_at_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
