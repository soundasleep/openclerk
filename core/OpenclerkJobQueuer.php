<?php

namespace Core;

use \Openclerk\Jobs\JobQueuer;
use \Openclerk\Jobs\Job;
use \Db\Connection;
use \Monolog\Logger;
use \Core\MyLogger;
use \DiscoveredComponents\Accounts;
use \DiscoveredComponents\Currencies;
use \DiscoveredComponents\Exchanges;

class OpenclerkJobQueuer extends JobQueuer {

  /**
   * Get all of the "standard" job types.
   * This provides information such as whether the job type is failable or not.
   * In the future, all of this should be moved into individual job definitions
   * particularly with {@code openclerk/jobs}, e.g. {@link FailableJob}
   */
  static function getStandardJobs() {

    $result = array();

    $ticker_jobs = array(
      array('table' => 'exchanges', 'type' => 'ticker', 'user_id' => get_site_config('system_user_id'), 'hours' => get_site_config('refresh_queue_hours_ticker'), 'query' => ' AND is_disabled=0'),
    );

    // address jobs are now named in common patterns
    // make sure to add the _block job below too if necessary
    $address_jobs = array();
    foreach (get_address_currencies() as $cur) {
      $address_jobs[] = array(
        'table' => 'addresses',
        'type' => 'address_' . $cur,
        'query' => " AND currency='$cur'",
      );
    }

    // premium address balance jobs needs to run much more frequently, but only for the system user (#412)
    foreach (get_address_currencies() as $cur) {
      $address_jobs[] = array(
        'table' => 'addresses',
        'type' => 'address_' . $cur,
        'query' => " AND currency='$cur' AND user_id='" . get_site_config('system_user_id') . "'",
        'hours' => get_site_config('refresh_queue_hours_system'),
      );
    }

    // account jobs are now named in common patterns
    $account_jobs = array();
    foreach (Accounts::getKeys() as $exchange) {
      if (!in_array($exchange, \DiscoveredComponents\Accounts::getDisabled())) {
        $account_jobs[] = array(
          'table' => 'accounts_' . $exchange,
          'type' => 'account_' . $exchange,
          'failure' => true,
        );
      }
    }

    // standard jobs involve an 'id' from a table and a 'user_id' from the same table (unless 'user_id' is set)
    // the table needs 'last_queue' unless 'always' is specified (in which case, it will always happen)
    // if no 'user_id' is specified, then the user will also be checked for disable status
    $standard_jobs = array_merge($ticker_jobs, $address_jobs, $account_jobs, array(
      array('table' => 'accounts_generic', 'type' => 'generic', 'failure' => true),
      array('table' => 'accounts_bit2c', 'type' => 'bit2c', 'failure' => true),
      array('table' => 'accounts_btce', 'type' => 'btce', 'failure' => true),
      array('table' => 'accounts_vircurex', 'type' => 'vircurex', 'failure' => true),
      array('table' => 'accounts_poolx', 'type' => 'poolx', 'failure' => true),
      array('table' => 'accounts_wemineltc', 'type' => 'wemineltc', 'failure' => true),
      array('table' => 'accounts_wemineftc', 'type' => 'wemineftc', 'failure' => true),
      array('table' => 'accounts_givemecoins', 'type' => 'givemecoins', 'failure' => true),
      array('table' => 'accounts_cryptostocks', 'type' => 'cryptostocks', 'failure' => true),
      array('table' => 'securities_cryptostocks', 'type' => 'securities_cryptostocks', 'user_id' => get_site_config('system_user_id'), 'failure' => true),
      array('table' => 'accounts_havelock', 'type' => 'havelock', 'failure' => true),
      array('table' => 'securities_havelock', 'type' => 'securities_havelock', 'user_id' => get_site_config('system_user_id'), 'failure' => true),
      array('table' => 'accounts_liteguardian', 'type' => 'liteguardian', 'failure' => true),
      array('table' => 'accounts_khore', 'type' => 'khore', 'failure' => true),
      array('table' => 'accounts_cexio', 'type' => 'cexio', 'failure' => true),
      array('table' => 'accounts_ghashio', 'type' => 'ghashio', 'failure' => true),
      array('table' => 'accounts_cryptotrade', 'type' => 'crypto-trade', 'failure' => true),
      array('table' => 'securities_cryptotrade', 'type' => 'securities_crypto-trade', 'user_id' => get_site_config('system_user_id'), 'failure' => true),
      array('table' => 'accounts_bitstamp', 'type' => 'bitstamp', 'failure' => true),
      array('table' => 'accounts_796', 'type' => '796', 'failure' => true),
      array('table' => 'securities_796', 'type' => 'securities_796', 'user_id' => get_site_config('system_user_id'), 'failure' => true),
      array('table' => 'accounts_kattare', 'type' => 'kattare', 'failure' => true),
      array('table' => 'accounts_litepooleu', 'type' => 'litepooleu', 'failure' => true),
      array('table' => 'accounts_litecoinpool', 'type' => 'litecoinpool', 'failure' => true),
      array('table' => 'accounts_hashfaster_doge', 'type' => 'hashfaster_doge', 'failure' => true),
      array('table' => 'accounts_hashfaster_ltc', 'type' => 'hashfaster_ltc', 'failure' => true),
      array('table' => 'accounts_hashfaster_ftc', 'type' => 'hashfaster_ftc', 'failure' => true),
      array('table' => 'accounts_triplemining', 'type' => 'triplemining', 'failure' => true),
      array('table' => 'accounts_ozcoin_ltc', 'type' => 'ozcoin_ltc', 'failure' => true),
      array('table' => 'accounts_ozcoin_btc', 'type' => 'ozcoin_btc', 'failure' => true),
      array('table' => 'accounts_scryptpools', 'type' => 'scryptpools', 'failure' => true),
      array('table' => 'accounts_justcoin', 'type' => 'justcoin', 'failure' => true),
      array('table' => 'accounts_multipool', 'type' => 'multipool', 'failure' => true),
      array('table' => 'accounts_ypool', 'type' => 'ypool', 'failure' => true),
      array('table' => 'accounts_coinbase', 'type' => 'coinbase', 'failure' => true),
      array('table' => 'accounts_litecoininvest', 'type' => 'litecoininvest', 'failure' => true),
      // securities_litecoininvest - we let securities_update handle this
      array('table' => 'accounts_miningpoolco', 'type' => 'miningpoolco', 'failure' => true),
      array('table' => 'accounts_vaultofsatoshi', 'type' => 'vaultofsatoshi', 'failure' => true),
      array('table' => 'accounts_teamdoge', 'type' => 'teamdoge', 'failure' => true),
      array('table' => 'accounts_nut2pools_ftc', 'type' => 'nut2pools_ftc', 'failure' => true),
      array('table' => 'accounts_cryptsy', 'type' => 'cryptsy', 'failure' => true),
      array('table' => 'accounts_kraken', 'type' => 'kraken', 'failure' => true),
      array('table' => 'accounts_bitmarket_pl', 'type' => 'bitmarket_pl', 'failure' => true),
      array('table' => 'accounts_poloniex', 'type' => 'poloniex', 'failure' => true),
      array('table' => 'accounts_mupool', 'type' => 'mupool', 'failure' => true),
      array('table' => 'accounts_anxpro', 'type' => 'anxpro', 'failure' => true),
      array('table' => 'accounts_bittrex', 'type' => 'bittrex', 'failure' => true),
      array('table' => 'accounts_nicehash', 'type' => 'nicehash', 'failure' => true),
      array('table' => 'accounts_westhash', 'type' => 'westhash', 'failure' => true),
      array('table' => 'accounts_eobot', 'type' => 'eobot', 'failure' => true),
      array('table' => 'accounts_hashtocoins', 'type' => 'hashtocoins', 'failure' => true),
      array('table' => 'accounts_btclevels', 'type' => 'btclevels', 'failure' => true),
      array('table' => 'accounts_bitnz', 'type' => 'bitnz', 'failure' => true),

      array('table' => 'exchanges', 'type' => 'reported_currencies', 'query' => ' AND track_reported_currencies=1 AND is_disabled=0 AND name="average"', 'user_id' => get_site_config('system_user_id')),

      array('table' => 'accounts_individual_cryptostocks', 'type' => 'individual_cryptostocks', 'failure' => true),
      array('table' => 'accounts_individual_havelock', 'type' => 'individual_havelock', 'failure' => true),
      array('table' => 'accounts_individual_cryptotrade', 'type' => 'individual_crypto-trade', 'failure' => true),
      array('table' => 'accounts_individual_796', 'type' => 'individual_796', 'failure' => true),
      array('table' => 'accounts_individual_litecoininvest', 'type' => 'individual_litecoininvest', 'failure' => true),
    ));

    if (get_site_config('allow_unsafe')) {
      // run unsafe jobs only if the flag has been set
      crypto_log("Running unsafe jobs.");
      $standard_jobs = array_merge($standard_jobs, array(
        // empty for now
      ));
    }

    $standard_jobs = array_merge($standard_jobs, array(
      array('table' => 'users', 'type' => 'delete_user', 'query' => ' AND is_deleted=1', 'always' => true, 'user_id_field' => 'id'),
      array('table' => 'users', 'type' => 'sum', 'user_id_field' => 'id'), /* does both sum and summaries now */
      array('table' => 'outstanding_premiums', 'type' => 'outstanding', 'query' => ' AND is_paid=0 AND is_unpaid=0', 'user_id' => get_site_config('system_user_id')),
      array('table' => 'users', 'type' => 'expiring', 'query' => ' AND is_premium=1
        AND is_reminder_sent=0
        AND NOT ISNULL(email) AND LENGTH(email) > 0
        AND NOW() > DATE_SUB(premium_expires, INTERVAL ' . get_site_config('premium_reminder_days') . ' DAY)', 'user_id' => get_site_config('system_user_id'), 'always' => true),
      array('table' => 'users', 'type' => 'expire', 'query' => ' AND is_premium=1
        AND NOW() > premium_expires', 'user_id' => get_site_config('system_user_id'), 'always' => true),
      array('table' => 'users', 'type' => 'disable_warning', 'query' => ' AND is_premium=0 AND is_disabled=0
        AND is_disable_warned=0 AND is_system=0
        AND DATE_ADD(GREATEST(IFNULL(last_login, 0),
            IFNULL(DATE_ADD(premium_expires, INTERVAL ' . get_site_config('user_expiry_days') . ' DAY), 0),
            created_at), INTERVAL ' . (get_site_config('user_expiry_days') * 0.8) . ' DAY) < NOW()', 'user_id' => get_site_config('system_user_id'), 'always' => true),
      array('table' => 'users', 'type' => 'disable', 'query' => ' AND is_premium=0 AND is_disabled=0
        AND is_disable_warned=1 AND is_system=0
        AND DATE_ADD(GREATEST(IFNULL(last_login, 0),
            IFNULL(DATE_ADD(premium_expires, INTERVAL ' . get_site_config('user_expiry_days') . ' DAY), 0),
            created_at), INTERVAL ' . (get_site_config('user_expiry_days')) . '+1 DAY) < NOW()', 'user_id' => get_site_config('system_user_id'), 'always' => true),
      array('table' => 'users', 'type' => 'securities_count', 'query' => ' AND is_disabled=0 AND is_system=0', 'queue_field' => 'securities_last_count_queue', 'user_id_field' => 'id'),
      array('table' => 'users', 'type' => 'transaction_creator', 'query' => ' AND is_disabled=0 AND is_system=0', 'queue_field' => 'last_tx_creator_queue', 'user_id_field' => 'id'),
      array('table' => 'securities_update', 'type' => 'securities_update', 'user_id' => get_site_config('system_user_id')),

      // transaction creators
      array('table' => 'transaction_creators', 'type' => 'transactions', 'failure' => true),

      // notifications support
      array('table' => 'notifications', 'type' => 'notification', 'query' => " AND period='hour'", 'failure' => true, 'hours' => 1),
      array('table' => 'notifications', 'type' => 'notification', 'query' => " AND period='day'", 'failure' => true, 'hours' => 24),
      array('table' => 'notifications', 'type' => 'notification', 'query' => " AND period='week'", 'failure' => true, 'hours' => 24 * 7),
      array('table' => 'notifications', 'type' => 'notification', 'query' => " AND period='month'", 'failure' => true, 'hours' => 24 * 7 * 30),
    ));

    foreach ($standard_jobs as $i => $s) {
      // add default parameters
      $standard_jobs[$i] += array(
        'failure' => false,
      );
    }

    return $standard_jobs;

  }

  /**
   * Get a list of all jobs that need to be queued, as an array of associative
   * arrays with (job_type, arg_id, [user_id]).
   *
   * This could use e.g. {@link JobTypeFinder}
   */
  function findJobs(Connection $db, Logger $logger) {

    $standard_jobs = self::getStandardJobs();

    $logger->info("Current time: " . date('r'));

    // get all disabled users
    $disabled = array();
    $q = $db->prepare("SELECT * FROM users WHERE is_disabled=1");
    $q->execute();
    while ($d = $q->fetch()) {
      $disabled[$d['id']] = $d;
    }

    foreach (array(true, false) as $is_premium_only) {
      $job_count = 0;

      foreach ($standard_jobs as $standard) {
        $always = isset($standard['always']) && $standard['always'];
        $field = isset($standard['user_id_field']) ? $standard['user_id_field'] : 'user_id';

        $query_extra = isset($standard['query']) ? $standard['query'] : "";
        $args_extra = isset($standard['args']) ? $standard['args'] : array();

        if (isset($standard['failure']) && $standard['failure']) {
          $query_extra .= " AND is_disabled=0";
        }

        $args = array();

        if (!$always) {
          // we want to run system jobs at least every 0.1 hours = 6 minutes
          $args[] = isset($standard['hours']) ? $standard['hours'] : ((isset($standard['user_id']) && $standard['user_id'] == get_site_config('system_user_id')) ? get_site_config('refresh_queue_hours_system') : ($is_premium_only ? get_site_config('refresh_queue_hours_premium') : get_site_config('refresh_queue_hours')));
        }

        $queue_field = isset($standard['queue_field']) ? $standard['queue_field'] : 'last_queue';

        if ($is_premium_only && (!isset($standard['user_id']) || $standard['user_id'] != get_site_config('system_user_id'))) {
          $query_extra .= " AND $field IN (SELECT id FROM users WHERE is_premium=1)";
        }

        // multiply queue_hours by 0.8 to ensure that user jobs are always executed within the specified timeframe
        try {
          $q = $db->prepare("SELECT * FROM " . $standard['table'] . " WHERE " . ($always ? "1" : "($queue_field <= DATE_SUB(NOW(), INTERVAL (? * 0.8) HOUR) OR ISNULL($queue_field))") . " $query_extra");
          $q->execute(array_join($args, $args_extra));
        } catch (\PdoException $e) {
          throw new \Exception("Could not find jobs for table '" . $standard['table'] . "': " . $e->getMessage(), $e->getCode(), $e);
        }
        $disabled_count = 0;
        while ($address = $q->fetch()) {
          $job = array(
            "job_type" => $standard['type'],
            "user_id" => isset($standard['user_id']) ? $standard['user_id'] : $address[$field], /* $field so we can select users.id as user_id */
            "arg_id" => $address['id'],

            // TODO eventually these should not be passed along; these are just passed
            // along for jobQueued() and debug printing
            "queue_field" => $queue_field,
            "object" => $address,
            "table" => $standard['table'],
          );

          // check that this user is not disabled
          if (isset($disabled[$job['user_id']])) {
            if ($disabled_count == 0) {
              $logger->info("Skipping job '" . $standard['type'] . "' for user " . $job['user_id'] . ": user is disabled");
            }
            $disabled_count++;
            continue;
          }

          $result[] = $job;
          $job_count++;
        }

        if ($disabled_count > 1) {
          $logger->info("Also skipped another " . number_format($disabled_count) . " " . $standard['type'] . " jobs due to disabled users");
        }
      }

      $logger->info($is_premium_only ? "Found $job_count premium jobs" : "Found $job_count general user jobs");
    }

    $block_jobs = array('version_check', 'vote_coins');
    foreach ($block_jobs as $name) {
      // as often as we can (or on request), run litecoin_block jobs
      $result[] = array(
        'job_type' => $name,
        'user_id' => get_site_config('system_user_id'),
        'arg_id' => -1,
      );
    }

    // block count jobs (using the new Currencies framework)
    foreach (\DiscoveredComponents\Currencies::getBlockCurrencies() as $cur) {
      $name = "blockcount_" . $cur;
      $result[] = array(
        'job_type' => $name,
        'user_id' => get_site_config('system_user_id'),
        'arg_id' => -1,
      );
    }

    // difficulty jobs (using the new Currencies framework)
    foreach (\DiscoveredComponents\Currencies::getDifficultyCurrencies() as $cur) {
      $name = "difficulty_" . $cur;
      $result[] = array(
        'job_type' => $name,
        'user_id' => get_site_config('system_user_id'),
        'arg_id' => -1,
      );
    }

    // markets jobs (using the new Exchanges framework: #400)
    foreach (\DiscoveredComponents\Exchanges::getKeys() as $exchange) {
      $name = "markets_" . $exchange;
      $result[] = array(
        'job_type' => $name,
        'user_id' => get_site_config('system_user_id'),
        'arg_id' => -1,
      );

      $name = "ticker_" . $exchange;
      $result[] = array(
        'job_type' => $name,
        'user_id' => get_site_config('system_user_id'),
        'arg_id' => -1,
      );
    }

    // supported currencies jobs (using the new Accounts framework)
    foreach (\DiscoveredComponents\Accounts::getKeys() as $key) {
      if (!in_array($key, \DiscoveredComponents\Accounts::getDisabled())) {
        $name = "currencies_" . $key;
        $result[] = array(
          'job_type' => $name,
          'user_id' => get_site_config('system_user_id'),
          'arg_id' => -1,
        );
      }
    }

    // supported hashrates jobs (using the new Accounts framework)
    foreach (\DiscoveredComponents\Accounts::getMiners() as $key) {
      if (!in_array($key, \DiscoveredComponents\Accounts::getDisabled())) {
        $name = "hashrates_" . $key;
        $result[] = array(
          'job_type' => $name,
          'user_id' => get_site_config('system_user_id'),
          'arg_id' => -1,
        );
      }
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

    if (isset($job['queue_field'])) {
      $logger->info("Added job " . print_r($printed_job, true) . " " . link_to(url_for('admin_run_job', array('job_id' => $job['id'], 'force' => 1)), "Run now"));

      $queue_field = $job['queue_field'];

      // only update last_queue if that field actually exists
      if (isset($job['object'][$queue_field]) || array_key_exists($queue_field, $job['object']) /* necessary to set last_queue when last_queue is null: isset() returns false on null */) {
        $q2 = $db->prepare("UPDATE " . $job['table'] . " SET $queue_field=NOW() WHERE id=?");
        $q2->execute(array($job['object']['id']));
      }

    } else {
      $logger->info("Existing job " . print_r($printed_job, true) . " " . link_to(url_for('admin_run_job', array('job_id' => $job['id'], 'force' => 1)), "Run now"));

    }

  }

}
