<?php
/**
 * Performance metrics functionality.
 */

/**
 * @deprecated TODO remove this function and config variable
 */
function performance_metrics_enabled() {
  return get_site_config('performance_metrics_enabled');
}

$_performance_metrics = array();

class PerformanceMetricsException extends Exception { }

/**
 * Called when the page starts.
 */
function performance_metrics_page_start() {
  Openclerk\Events::trigger('page_start', null);

  // TODO remove when job metrics are being captured
  global $_performance_metrics;
  $_performance_metrics['page_start'] = microtime(true);
}

/**
 * Called when the page is complete.
 */
function performance_metrics_page_end() {
  Openclerk\Events::trigger('page_end', null);
}

/**
 * Called when a job has been executed by the job framework.
 * {@link #performance_metrics_page_end()} can still be called for database metrics eftc.
 */
function performance_metrics_job_complete($job = null, $runtime_exception = null) {
  if (!performance_metrics_enabled()) {
    return;
  }
  global $_performance_metrics;
  $job_time = microtime(true) - $_performance_metrics['page_start'];
  if (isset($_performance_metrics['job_complete'])) {
    throw new PerformanceMetricsException("job_complete called twice");
  }
  $_performance_metrics['job_complete'] = true;

  // "What jobs take the longest?"
  // "How many jobs are running per hour?"
  // "How many ticker jobs are running per hour?"
  // "What jobs have the most database queries?"
  // "What jobs spend the most time in PHP as opposed to the database?"
  // "Which jobs time out the most?"
  // "How many blockchain requests fail?"
  // "What jobs take the longest requesting URLs?"
  if ($job) {
    $query = "INSERT INTO performance_metrics_jobs SET job_type=:job_type, arg0=:arg0, time_taken=:time_taken,
        job_failure=:job_failure, runtime_exception=:runtime_exception";
    $args = array(
      'job_type' => $job['job_type'],
      'arg0' => isset($job['arg0']) ? $job['arg0'] : null,
      'time_taken' => $job_time * 1000, /* save in ms */
      'job_failure' => $job['is_error'] ? 1 : 0,
      'runtime_exception' => $runtime_exception ? get_class($runtime_exception) : null,
    );

    $q = db()->prepare($query);
    $q->execute($args);
  }
}

/**
 * Called when the job queue has been executed by the job framework.
 * {@link #performance_metrics_page_end()} can still be called for database metrics etc.
 */
function performance_metrics_queue_complete($user_id, $priority, $job_types, $premium_only) {
  if (!performance_metrics_enabled()) {
    return;
  }
  global $_performance_metrics;
  $queue_time = microtime(true) - $_performance_metrics['page_start'];
  if (isset($_performance_metrics['queue_complete'])) {
    throw new PerformanceMetricsException("queue_complete called twice");
  }
  $_performance_metrics['queue_complete'] = true;

  // "How many jobs are being queued at once?"
  // "Which queue types take the longest?"
  $query = "INSERT INTO performance_metrics_queues SET time_taken=:time_taken, user_id=:user_id, priority=:priority, job_types=:job_types,
    premium_only=:premium_only";
  $args = array(
    'time_taken' => $queue_time * 1000, /* save in ms */
    'user_id' => $user_id ? $user_id : null,
    'priority' => $priority ? $priority : null,
    'job_types' => $job_types ? substr($job_types, 0, 255) : null,
    'premium_only' => $premium_only ? 1 : 0,
  );

  $q = db()->prepare($query);
  $q->execute($args);

}

/**
 * Called when a graph has been rendered by the job framework.
 * {@link #performance_metrics_page_end()} can still be called for database metrics etc.
 */
function performance_metrics_graph_complete($graph) {
  if (!performance_metrics_enabled()) {
    return;
  }
  global $_performance_metrics;
  $graph_time = microtime(true) - $_performance_metrics['page_start'];
  if (isset($_performance_metrics['graph_complete'])) {
    throw new PerformanceMetricsException("graph_complete called twice");
  }
  $_performance_metrics['graph_complete'] = true;

  // "What graph types take the longest to render?"
  // "What are the most common graph types?"
  // "How many ticker graphs are being requested?"
  if ($graph) {
    $query = "INSERT INTO performance_metrics_graphs SET graph_type=:graph_type, time_taken=:time_taken, is_logged_in=:is_logged_in,
      days=:days, has_technicals=:has_technicals";
    $args = array(
      'graph_type' => substr($graph['graph_type'], 0, 32),
      'time_taken' => $graph_time * 1000, /* save in ms */
      'is_logged_in' => user_logged_in() ? 1 : 0,
      'days' => $graph['days'] ? $graph['days'] : null,
      'has_technicals' => isset($graph['technicals']) && $graph['technicals'] ? 1 : 0,
    );

    $q = db()->prepare($query);
    $q->execute($args);
  }
}
