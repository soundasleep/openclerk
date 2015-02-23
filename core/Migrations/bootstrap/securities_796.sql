/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securities_796` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_queue` timestamp NULL DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `api_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `failures` tinyint(4) NOT NULL DEFAULT '0',
  `first_failure` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `last_queue` (`last_queue`),
  KEY `is_disabled` (`is_disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `securities_796` (`id`, `created_at`, `last_queue`, `name`, `title`, `api_name`, `user_id`, `is_disabled`, `failures`, `first_failure`) VALUES (1,'2015-02-20 02:42:18',NULL,'mri','796Xchange-MRI','mri',100,0,0,NULL),(2,'2015-02-20 02:42:18',NULL,'asicminer','ASICMINER-796','asicminer',100,0,0,NULL),(3,'2015-02-20 02:42:18',NULL,'bd','BTC-DICE-796','bd',100,1,0,NULL),(4,'2015-02-20 02:42:31',NULL,'rsm','RSM','rsm',100,0,0,NULL);
