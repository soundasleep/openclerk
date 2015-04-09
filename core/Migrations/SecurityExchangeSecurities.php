<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Store supported securities for each security exchange.
 * Issue #400
 */
class SecurityExchangeSecurities extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE security_exchange_securities (
      id int not null auto_increment primary key,
      created_at timestamp not null default current_timestamp,
      last_queue timestamp null,

      exchange varchar(32) not null,
      currency varchar(3) not null,
      security varchar(64) not null,

      is_disabled tinyint not null default 0,
      failures tinyint not null default 0,
      first_failure timestamp null,
      is_disabled_manually tinyint not null default 0,

      INDEX(last_queue),
      INDEX(is_disabled),
      INDEX(is_disabled_manually),

      INDEX(exchange, security, currency)
    );");
    return $q->execute();
  }

}
