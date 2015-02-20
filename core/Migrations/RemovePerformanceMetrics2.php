<?php

namespace Core\Migrations;

/**
 * We need to remove the old tables;
 * this table will now be managed by the openclerk/metrics component.
 */
class RemovePerformanceMetrics2 extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("DROP TABLE performance_metrics_urls; DROP TABLE performance_metrics_slow_urls; DROP TABLE performance_metrics_repeated_urls;");
    return $q->execute();
  }

}
