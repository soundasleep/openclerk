<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Store supported currencies for each account.
 * Issue #401
 */
class AccountCurrencies extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE account_currencies (
      id int not null auto_increment primary key,
      created_at timestamp not null default current_timestamp,

      exchange varchar(32) not null,
      currency varchar(3) not null,

      UNIQUE(exchange, currency)
    );");
    return $q->execute();
  }

}
