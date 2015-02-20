/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exchanges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `last_queue` timestamp NULL DEFAULT NULL,
  `track_reported_currencies` tinyint(4) NOT NULL DEFAULT '0',
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `last_queue` (`last_queue`),
  KEY `name_2` (`name`),
  KEY `track_reported_currencies` (`track_reported_currencies`),
  KEY `is_disabled` (`is_disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `exchanges` (`id`, `created_at`, `name`, `last_queue`, `track_reported_currencies`, `is_disabled`) VALUES (1,'2015-02-20 02:42:14','btce',NULL,1,0),(2,'2015-02-20 02:42:14','bitnz',NULL,0,0),(3,'2015-02-20 02:42:14','mtgox',NULL,0,1),(4,'2015-02-20 02:42:14','vircurex',NULL,1,0),(5,'2015-02-20 02:42:15','themoneyconverter',NULL,1,0),(6,'2015-02-20 02:42:15','virtex',NULL,0,0),(7,'2015-02-20 02:42:15','bitstamp',NULL,0,0),(8,'2015-02-20 02:42:16','cexio',NULL,1,0),(9,'2015-02-20 02:42:16','crypto-trade',NULL,1,0),(10,'2015-02-20 02:42:20','btcchina',NULL,0,0),(11,'2015-02-20 02:42:20','cryptsy',NULL,1,0),(12,'2015-02-20 02:42:21','coins-e',NULL,1,0),(13,'2015-02-20 02:42:22','bitcurex',NULL,0,0),(14,'2015-02-20 02:42:22','justcoin',NULL,1,0),(15,'2015-02-20 02:42:22','coinbase',NULL,1,0),(16,'2015-02-20 02:42:23','vaultofsatoshi',NULL,1,0),(17,'2015-02-20 02:42:23','bit2c',NULL,0,0),(18,'2015-02-20 02:42:26','kraken',NULL,1,0),(19,'2015-02-20 02:42:30','average',NULL,0,0),(20,'2015-02-20 02:42:30','bitmarket_pl',NULL,1,0),(21,'2015-02-20 02:42:30','poloniex',NULL,1,0),(22,'2015-02-20 02:42:30','mintpal',NULL,1,1),(23,'2015-02-20 02:42:30','anxpro',NULL,1,0),(24,'2015-02-20 02:42:30','itbit',NULL,0,0),(25,'2015-02-20 02:42:30','bittrex',NULL,1,0),(26,'2015-02-20 02:42:31','bter',NULL,1,0);
