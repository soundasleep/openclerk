/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `average_market_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `currency1` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `currency2` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `market_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currency1` (`currency1`,`currency2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
