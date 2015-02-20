/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outstanding_premiums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` timestamp NULL DEFAULT NULL,
  `is_paid` tinyint(4) NOT NULL DEFAULT '0',
  `is_unpaid` tinyint(4) NOT NULL DEFAULT '0',
  `last_queue` timestamp NULL DEFAULT NULL,
  `premium_address_id` int(11) NOT NULL,
  `balance` decimal(24,8) DEFAULT NULL,
  `months` tinyint(4) NOT NULL,
  `years` tinyint(4) NOT NULL,
  `address_id` int(11) DEFAULT NULL,
  `last_reminder` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `paid_balance` decimal(24,8) DEFAULT '0.00000000',
  `last_balance` decimal(24,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `address_id` (`address_id`),
  KEY `premium_address_id` (`premium_address_id`),
  KEY `is_paid` (`is_paid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
