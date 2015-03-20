<?php

/**
 * Batch script: find a job to execute, and execute it.
 * Run test jobs only.
 * Uses the new openclerk/jobs framework.
 */

define('USE_MASTER_DB', true);    // always use the master database for selects!

if (!defined('ADMIN_RUN_JOB')) {
  require(__DIR__ . "/../inc/global.php");
}
require(__DIR__ . "/_batch.php");
require(__DIR__ . "/_batch_insert.php");

require_batch_key();

use \Openclerk\Jobs\JobRunner;
use \Openclerk\Jobs\Job;
use \Db\Connection;
use \Monolog\Logger;
use \Core\MyLogger;

/**
 * Run test jobs.
 */
class OpenclerkJobRunnerTest extends \Core\OpenclerkJobRunner {

  /**
   * Find a job that is a test job.
   */
  function findJob(Connection $db, Logger $logger) {
    if ($this->isJobsDisabled($logger)) {
      return false;
    }

    $q = $db->prepare("SELECT * FROM jobs WHERE is_test_job=1 AND " . $this->defaultFindJobQuery() . " LIMIT 1");
    $q->execute();
    return $q->fetch();
  }

}

$logger = new \Monolog\Logger("batch_run");
$logger->pushHandler(new \Core\MyLogger());

$runner = new OpenclerkJobRunnerTest();
$runner->runOne(db(), $logger);

