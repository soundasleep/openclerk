<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Store the latest ticker values for each security.
 * Issue #469
 */
class SecurityTicker extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE security_ticker (
      id int not null auto_increment primary key,
      created_at timestamp not null default current_timestamp,

      exchange varchar(32) not null,
      security varchar(64) not null,

      last_trade decimal(24,8) null,
      ask decimal(24,8) null,
      bid decimal(24,8) null,
      volume decimal(24,8) null,
      units int null,

      is_daily_data tinyint not null default 0,
      created_at_day mediumint not null,
      job_id int null,

      INDEX(exchange, security),
      INDEX(is_daily_data, created_at_day)
    );");
    return $q->execute();
  }

}
