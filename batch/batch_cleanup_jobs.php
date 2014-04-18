<?php

/**
 * A batch script to clean up old jobs.
 * This always executes (no job framework) so it should be used sparingly or as necessary.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch cleanup jobs", "batch_cleanup_jobs");

crypto_log("Current time: " . date('r'));

// simply delete all jobs that haven't executed and are over three months old
$q = db_master()->prepare("DELETE FROM jobs WHERE is_executed=1 AND executed_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
$q->execute(array());

crypto_log("Deleted old jobs.");

batch_footer();
