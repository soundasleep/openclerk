<?php

namespace Core;

use \Openclerk\Jobs\JobRunner;
use \Openclerk\Jobs\Job;
use \Db\Connection;
use \Monolog\Logger;
use \Core\MyLogger;

class OpenclerkJobRunner extends JobRunner {

  /**
   * Just creates {@link GenericOpenclerkJob}s for now
   */
  function createJob($job, Connection $db, Logger $logger) {
    $logger->info(link_to(url_for('admin_run_job', array('job_id' => $job['id'], 'force' => 1)), "Run again"));
    $logger->info(link_to(url_for('admin_run_job'), "Next job"));

    // mark execution_started
    $q = $db->prepare("UPDATE jobs SET execution_started=NOW() WHERE id=?");
    $q->execute(array($job['id']));

    return new GenericOpenclerkJob($job, $db, $logger);
  }

  /**
   * If we have a ?job_id parameter, then select only this job
   */
  function findJob(Connection $db, Logger $logger) {
    if ($this->isJobsDisabled($logger)) {
      return false;
    }

    // mark all once-failed test jobs as failing
    $q = $db->prepare("SELECT * FROM jobs WHERE is_executing=0 AND is_error=0 AND is_test_job=1 AND execution_count >= ?");
    $q->execute(array(1));
    if ($failed = $q->fetchAll()) {
      $logger->info("Found " . number_format(count($failed)) . " test jobs that have failed once");
      foreach ($failed as $job) {
        $q = $db->prepare("UPDATE jobs SET is_executed=1,is_error=1 WHERE id=?");
        $q->execute(array($job['id']));
        $logger->info("Marked test job " . $job['id'] . " as failed");
      }
    }

    // mark all jobs that have been executing for far too long as failed
    $q = $db->prepare("SELECT * FROM jobs WHERE is_executing=1 AND execution_started <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $q->execute(array());
    if ($timeout = $q->fetchAll()) {
      $logger->info("Found " . number_format(count($timeout)) . " jobs that have timed out");
      foreach ($timeout as $job) {
        $q = $db->prepare("UPDATE jobs SET is_timeout=1, is_executing=0 WHERE id=?");
        $q->execute(array($job['id']));
        $logger->info("Marked job " . $job['id'] . " as timed out");
      }
    }

    if (require_get("job_id", false)) {
      $q = $db->prepare("SELECT * FROM jobs WHERE id=? LIMIT 1");
      $q->execute(array(require_get("job_id")));
      return $q->fetch();

    } else {
      return parent::findJob($db, $logger);
    }
  }

  function isJobsDisabled(Logger $logger) {
    if (!get_site_config('jobs_enabled')) {
      $logger->info("Running jobs is disabled");
      return true;
    }
    return false;
  }

}
