<?php

/**
 * Batch script: find a job to execute, and execute it.
 * Run ticker jobs only.
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
 * Run jobs of a given prefix.
 */
class OpenclerkJobRunnerType extends \Core\OpenclerkJobRunner {

  var $job_prefix;

  function __construct($job_prefix) {
    parent::__construct();
    $this->job_prefix = $job_prefix;
  }

  /**
   * Find a job that starts with the given prefix
   */
  function findJob(Connection $db, Logger $logger) {
    $q = $db->prepare("SELECT * FROM jobs WHERE (job_prefix=? OR job_type=?) AND " . $this->defaultFindJobQuery() . " LIMIT 1");
    $q->execute(array($this->job_prefix, $this->job_prefix));
    return $q->fetch();
  }

}

if (isset($argv)) {
  $job_prefix = $argv[2];
} else {
  $job_prefix = require_get("type");
}

$logger = new \Monolog\Logger("batch_run");
$logger->pushHandler(new \Core\MyLogger());
$logger->info("Running job type '$job_prefix'");

$runner = new OpenclerkJobRunnerType($job_prefix);
$runner->runOne(db(), $logger);

