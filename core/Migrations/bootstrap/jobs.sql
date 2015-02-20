/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `priority` tinyint(4) NOT NULL DEFAULT '10',
  `job_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `arg_id` int(11) DEFAULT NULL,
  `is_executed` tinyint(4) NOT NULL DEFAULT '0',
  `is_error` tinyint(4) NOT NULL DEFAULT '0',
  `executed_at` timestamp NULL DEFAULT NULL,
  `execution_count` tinyint(4) NOT NULL DEFAULT '0',
  `is_executing` tinyint(4) NOT NULL DEFAULT '0',
  `is_recent` tinyint(4) NOT NULL DEFAULT '0',
  `is_test_job` tinyint(4) NOT NULL DEFAULT '0',
  `execution_started` timestamp NULL DEFAULT NULL,
  `is_timeout` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `job_type` (`job_type`),
  KEY `priority` (`priority`),
  KEY `user_id` (`user_id`),
  KEY `is_executed` (`is_executed`),
  KEY `is_error` (`is_error`),
  KEY `is_executing` (`is_executing`),
  KEY `is_recent` (`is_recent`),
  KEY `is_test_job` (`is_test_job`),
  KEY `is_timeout` (`is_timeout`),
  KEY `is_recent_2` (`is_recent`,`user_id`,`job_type`,`arg_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
