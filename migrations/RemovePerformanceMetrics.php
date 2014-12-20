<?php

namespace Migrations;

/**
 * We need to remove the old tables;
 * this table will now be managed by the openclerk/metrics component.
 */
class RemovePerformanceMetrics extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("DROP TABLE performance_metrics_pages; DROP TABLE performance_report_slow_pages; DROP TABLE performance_reports;");
    return $q->execute();
  }

}
