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

    return new GenericOpenclerkJob($job, $db, $logger);
  }

  /**
   * If we have a ?job_id parameter, then select only this job
   */
  function findJob(Connection $db, Logger $logger) {
    if ($this->isJobsDisabled($logger)) {
      return false;
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
