<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Store the latest ticker values for each security.
 * Issue #469
 */
class SecurityTickerRecent extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE security_ticker_recent (
      id int not null auto_increment primary key,
      created_at timestamp not null default current_timestamp,

      exchange varchar(32) not null,
      security varchar(64) not null,

      last_trade decimal(24,8) null,
      ask decimal(24,8) null,
      bid decimal(24,8) null,
      volume decimal(24,8) null,
      units int null,
      job_id int null,

      INDEX(exchange, security)
    );");
    return $q->execute();
  }

  function isApplied(\Db\Connection $db) {
    return $this->tableExists($db, "security_ticker_recent");
  }

}
