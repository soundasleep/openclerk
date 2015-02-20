/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_automatic` tinyint(4) NOT NULL DEFAULT '0',
  `transaction_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `transaction_date_day` mediumint(9) NOT NULL,
  `exchange` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `account_id` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency1` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `value1` decimal(24,8) NOT NULL,
  `currency2` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value2` decimal(24,8) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`exchange`,`account_id`,`transaction_date`),
  KEY `exchange` (`exchange`),
  KEY `currency1` (`currency1`),
  KEY `currency2` (`currency2`),
  KEY `transaction_date_day` (`transaction_date_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
