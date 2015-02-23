/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_queue` timestamp NULL DEFAULT NULL,
  `last_value` decimal(24,8) DEFAULT NULL,
  `notification_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `type_id` int(11) NOT NULL,
  `trigger_condition` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `trigger_value` decimal(24,8) DEFAULT NULL,
  `is_percent` tinyint(4) NOT NULL DEFAULT '0',
  `period` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `is_notified` tinyint(4) NOT NULL DEFAULT '0',
  `last_notification` timestamp NULL DEFAULT NULL,
  `notifications_sent` int(11) NOT NULL DEFAULT '0',
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `failures` tinyint(4) NOT NULL DEFAULT '0',
  `first_failure` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `notification_type` (`notification_type`,`type_id`),
  KEY `last_queue` (`last_queue`),
  KEY `is_disabled` (`is_disabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
