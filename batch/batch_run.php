<?php

/**
 * Batch script: find a job to execute, and execute it.
 * Uses the new openclerk/jobs framework.
 */

define('USE_MASTER_DB', true);    // always use the master database for selects!

if (!defined('ADMIN_RUN_JOB')) {
  require(__DIR__ . "/../inc/global.php");
}
require(__DIR__ . "/_batch.php");
require(__DIR__ . "/_batch_insert.php");

require_batch_key();

$logger = new \Monolog\Logger("batch_run");
$logger->pushHandler(new \Core\MyLogger());

$runner = new \Core\OpenclerkJobRunner();
$runner->runOne(db(), $logger);
