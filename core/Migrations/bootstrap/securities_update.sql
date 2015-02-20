/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securities_update` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_queue` timestamp NULL DEFAULT NULL,
  `exchange` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_queue` (`last_queue`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `securities_update` (`id`, `created_at`, `last_queue`, `exchange`) VALUES (3,'2015-02-20 02:42:14',NULL,'havelock'),(5,'2015-02-20 02:42:20',NULL,'eligius'),(6,'2015-02-20 02:42:22',NULL,'litecoininvest');
