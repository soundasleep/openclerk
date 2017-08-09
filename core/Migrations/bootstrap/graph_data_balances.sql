/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_data_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `exchange` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `account_id` int(11) NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `data_date` timestamp NULL,
  `samples` int(11) NOT NULL,
  `balance_min` decimal(24,8) DEFAULT NULL,
  `balance_opening` decimal(24,8) DEFAULT NULL,
  `balance_closing` decimal(24,8) DEFAULT NULL,
  `balance_max` decimal(24,8) DEFAULT NULL,
  `balance_stdev` float DEFAULT NULL,
  `data_date_day` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_2` (`user_id`,`exchange`,`account_id`,`currency`,`data_date`),
  KEY `user_id` (`user_id`),
  KEY `exchange` (`exchange`),
  KEY `data_date` (`data_date`),
  KEY `data_date_day` (`data_date_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
