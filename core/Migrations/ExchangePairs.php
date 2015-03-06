<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Store supported exchange pairs for each exchange.
 * Issue #400
 */
class ExchangePairs extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE exchange_pairs (
      id int not null auto_increment primary key,
      created_at timestamp not null default current_timestamp,

      exchange varchar(32) not null,
      currency1 varchar(3) not null,
      currency2 varchar(3) not null,

      INDEX(exchange, currency1, currency2)
    );");
    return $q->execute();
  }

}
