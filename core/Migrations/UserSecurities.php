<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Store the securities that each user owns. Replaces the `securities` table.
 * Issue #469
 */
class UserSecurities extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE user_securities (
      id int not null auto_increment primary key,
      created_at timestamp not null default current_timestamp,
      user_id int not null,

      exchange varchar(32) not null,
      security varchar(64) not null,

      quantity int not null,

      is_recent tinyint not null default 0,
      account_id int not null,

      INDEX(user_id, exchange, security),
      INDEX(is_recent),
      INDEX(account_id)
    );");
    return $q->execute();
  }

  function isApplied(\Db\Connection $db) {
    return $this->tableExists($db, "user_securities");
  }

}
