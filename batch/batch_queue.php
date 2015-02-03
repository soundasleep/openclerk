<?php

/**
 * Batch script: find new jobs to queue up.
 * Uses the new openclerk/jobs framework.
 */

define('USE_MASTER_DB', true);    // always use the master database for selects!

if (!defined('ADMIN_RUN_JOB')) {
  require(__DIR__ . "/../inc/global.php");
}
require(__DIR__ . "/_batch.php");
require(__DIR__ . "/_batch_insert.php");

require_batch_key();

$logger = new \Monolog\Logger("batch_queue");
$logger->pushHandler(new \Core\MyLogger());

$runner = new \Core\OpenclerkJobQueuer();
$runner->queue(db(), $logger);
