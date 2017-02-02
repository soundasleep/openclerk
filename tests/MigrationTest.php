<?php

require_once(__DIR__ . "/../inc/global.php");

class BadMigration extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("CREATE TABLE `bad_timestamp_Table` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `job_first` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    return $q->execute();
  }
}

class BadMigrationCollection extends \Db\Migration {
  /**
   * Get all parent {@link Migration}s that this migration depends on, as a list
   */
  function getParents() {
    return array(new \Db\BaseMigration(), new BadMigration());
  }
}

/**
 * Test that openclerk/db can migrate successfully.
 */
class MigrationTest extends PHPUnit_Framework_TestCase {

  function testBadMigration() {
    $migration = new BadMigration();

    $this->assertTrue($migration->hasPending(db()));
    try {
      $migration->apply(db());
      $this->fail("Should not have been able to apply bad migration");
    } catch (PDOException $e) {
      // expected
    }
  }

  function testBadMigrationCollection() {
    $migration = new BadMigrationCollection();

    $this->assertTrue($migration->hasPending(db()));
    try {
      $migration->install(db(), $this->logger());
      $this->fail("Should not have been able to apply bad migration");
    } catch (PDOException $e) {
      // expected
    }
  }

  function logger() {
    return new \Monolog\Logger("migration-test");
  }

}
