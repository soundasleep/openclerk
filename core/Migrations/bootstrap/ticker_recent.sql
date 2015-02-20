/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticker_recent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `exchange` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `currency1` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `currency2` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `last_trade` decimal(24,8) DEFAULT NULL,
  `ask` decimal(24,8) DEFAULT NULL,
  `bid` decimal(24,8) DEFAULT NULL,
  `volume` decimal(24,8) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exchange` (`exchange`,`currency1`,`currency2`),
  KEY `job_id` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
