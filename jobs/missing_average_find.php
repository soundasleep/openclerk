<?php

use \Openclerk\Jobs\JobQueuer;
use \Db\Connection;
use \Monolog\Logger;

/**
 * Find market data which is missing (#457), but only works
 * for data that is still within the `ticker` table (i.e. not in `ticker_historical`).
 */

class MissingAverageJobQueuer extends JobQueuer {

  /**
   * Get a list of all jobs that need to be queued, as an array of associative
   * arrays with (job_type, arg_id, [user_id]).
   *
   * This could use e.g. {@link JobTypeFinder}
   */
  function findJobs(Connection $db, Logger $logger) {
    $logger->info("Creating temporary table");

    $q = $db->prepare("CREATE TABLE temp (
        created_at_day INT NOT NULL,
        INDEX(created_at_day)
      )");
    $q->execute();

    $logger->info("Inserting into temporary table");

    $q = $db->prepare("INSERT INTO temp (SELECT created_at_day FROM ticker WHERE exchange = 'average' GROUP BY created_at_day)");
    $q->execute();

    $logger->info("Querying");

    $q = $db->prepare("SELECT created_at_day, min(created_at) as date, count(*) as c
        FROM ticker
        WHERE exchange <> 'average' AND exchange <> 'themoneyconverter' and is_daily_data=1 and created_at_day not in (SELECT created_at_day FROM temp)
        GROUP BY created_at_day");
    $q->execute();
    $missing = $q->fetchAll();

    $logger->info("Dropping temporary table");

    $q = $db->prepare("DROP TABLE temp");
    $q->execute();

    $logger->info("Found " . number_format(count($missing)) . " days of missing average data");

    $result = array();
    foreach ($missing as $row) {
      $logger->info("Average data for " . $row['date'] . " can be reconstructed from " . number_format($row['c']) . " ticker instances");

      $result[] = array(
        'job_type' => 'missing_average',
        'arg_id' => $row['created_at_day'],
        'user_id' => get_site_config('system_user_id'),
      );
    }

    return $result;
  }

  /**
   * The given job has been queued up, so we can mark it as successfully queued.
   */
  function jobQueued(Connection $db, Logger $logger, $job) {
    $printed_job = array(
      'id' => $job['id'],
      'job_type' => $job['job_type'],
      'user_id' => $job['user_id'],
      'arg_id' => $job['arg_id'],
    );

    $logger->info("Added job " . print_r($printed_job, true) . " " . link_to(url_for('admin_run_job', array('job_id' => $job['id'], 'force' => 1)), "Run now"));
  }

}

$logger = new \Monolog\Logger("batch_queue");
$logger->pushHandler(new \Core\MyLogger());

$runner = new MissingAverageJobQueuer();
$runner->queue(db(), $logger);
