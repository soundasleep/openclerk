/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securities_cryptotrade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_queue` timestamp NULL DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `currency` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `failures` tinyint(4) NOT NULL DEFAULT '0',
  `first_failure` timestamp NULL DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_queue` (`last_queue`),
  KEY `name` (`name`),
  KEY `is_disabled` (`is_disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `securities_cryptotrade` (`id`, `created_at`, `last_queue`, `name`, `currency`, `title`, `is_disabled`, `failures`, `first_failure`, `user_id`) VALUES (1,'2015-02-20 02:42:16',NULL,'CTB','btc','CRYPTO-TRADE-BTC',0,0,NULL,100),(2,'2015-02-20 02:42:16',NULL,'CTL','ltc','CRYPTO-TRADE-LTC',0,0,NULL,100),(3,'2015-02-20 02:42:16',NULL,'ESB','btc','ESECURITYSA-BTC',0,0,NULL,100),(4,'2015-02-20 02:42:16',NULL,'ESL','ltc','ESECURITYSA-LTC',0,0,NULL,100),(5,'2015-02-20 02:42:22',NULL,'AMC','btc','ACTIVEMININGCORP',0,0,NULL,100),(6,'2015-02-20 02:42:22',NULL,'GGB','btc','GALTS-GULCH-ORGANIC',0,0,NULL,100);
