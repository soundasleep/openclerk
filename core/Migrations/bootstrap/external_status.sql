/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `external_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `job_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `job_count` int(11) NOT NULL,
  `job_errors` int(11) NOT NULL,
  `job_first` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `job_last` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sample_size` int(11) NOT NULL,
  `is_recent` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `is_recent` (`is_recent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
