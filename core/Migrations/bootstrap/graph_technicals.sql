/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_technicals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `graph_id` int(11) NOT NULL,
  `technical_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `technical_period` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graph_id` (`graph_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
