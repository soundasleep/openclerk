/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `performance_metrics_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_taken` int(11) NOT NULL,
  `script_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_logged_in` tinyint(4) NOT NULL,
  `db_prepares` int(11) DEFAULT NULL,
  `db_executes` int(11) DEFAULT NULL,
  `db_fetches` int(11) DEFAULT NULL,
  `db_fetch_alls` int(11) DEFAULT NULL,
  `db_prepare_time` int(11) DEFAULT NULL,
  `db_execute_time` int(11) DEFAULT NULL,
  `db_fetch_time` int(11) DEFAULT NULL,
  `db_fetch_all_time` int(11) DEFAULT NULL,
  `curl_requests` int(11) DEFAULT NULL,
  `curl_request_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `script_name` (`script_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
