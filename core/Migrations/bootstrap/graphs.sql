/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `graph_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `arg0` int(11) DEFAULT NULL,
  `width` tinyint(4) DEFAULT '2',
  `height` tinyint(4) DEFAULT '2',
  `page_order` smallint(6) DEFAULT '0',
  `is_removed` tinyint(4) NOT NULL DEFAULT '0',
  `days` int(11) DEFAULT NULL,
  `string0` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_managed` tinyint(4) NOT NULL DEFAULT '0',
  `delta` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `is_removed` (`is_removed`),
  KEY `is_managed` (`is_managed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
