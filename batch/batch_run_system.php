<?php

/**
 * Batch script: find a job to execute, and execute it.
 * Run system user jobs only.
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
 * Run jobs from system users.
 */
class OpenclerkJobRunnerSystem extends \Core\OpenclerkJobRunner {

  /**
   * Find a job that belongs to the system user.
   */
  function findJob(Connection $db, Logger $logger) {
    $q = $db->prepare("SELECT * FROM jobs WHERE user_id = ? AND " . $this->defaultFindJobQuery() . " LIMIT 1");
    $q->execute(get_site_config('system_user_id'));
    return $q->fetch();
  }

}

$logger = new \Monolog\Logger("batch_run");
$logger->pushHandler(new \Core\MyLogger());

$runner = new OpenclerkJobRunnerSystem();
$runner->runOne(db(), $logger);

