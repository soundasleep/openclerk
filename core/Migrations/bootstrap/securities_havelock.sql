/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securities_havelock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_queue` timestamp NULL DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `failures` tinyint(4) NOT NULL DEFAULT '0',
  `first_failure` timestamp NULL DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `is_disabled_manually` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `last_queue` (`last_queue`),
  KEY `name` (`name`),
  KEY `is_disabled` (`is_disabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
