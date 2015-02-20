/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_data_ticker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `exchange` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `currency1` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency2` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `samples` int(11) NOT NULL,
  `ask` decimal(24,8) DEFAULT NULL,
  `bid` decimal(24,8) DEFAULT NULL,
  `volume` decimal(24,8) DEFAULT NULL,
  `last_trade_min` decimal(24,8) DEFAULT NULL,
  `last_trade_opening` decimal(24,8) DEFAULT NULL,
  `last_trade_closing` decimal(24,8) DEFAULT NULL,
  `last_trade_max` decimal(24,8) DEFAULT NULL,
  `last_trade_stdev` float DEFAULT NULL,
  `data_date_day` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exchange_2` (`exchange`,`currency1`,`currency2`,`data_date`),
  KEY `exchange` (`exchange`),
  KEY `currency1` (`currency1`),
  KEY `currency2` (`currency2`),
  KEY `data_date` (`data_date`),
  KEY `data_date_day` (`data_date_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
