<?php

namespace Core;

use \Openclerk\Jobs\JobRunner;
use \Openclerk\Jobs\Job;
use \Db\Connection;
use \Monolog\Logger;
use \Core\MyLogger;
use \JobException;

class GenericOpenclerkJob implements Job {

  function __construct($job) {
    $this->job = $job;
  }

  function run(Connection $db, Logger $logger) {
    $job = $this->job;

    switch ($job['job_type']) {
      // ticker jobs
      case "ticker":
        require(__DIR__ . "/../jobs/ticker.php");
        break;

      case "reported_currencies":
        require(__DIR__ . "/../jobs/reported_currencies.php");
        break;

      // account jobs
      case "generic":
        require(__DIR__ . "/../jobs/generic.php");
        break;

      case "bit2c":
        require(__DIR__ . "/../jobs/bit2c.php");
        break;

      case "btce":
        require(__DIR__ . "/../jobs/btce.php");
        break;

      case "vircurex":
        require(__DIR__ . "/../jobs/vircurex.php");
        break;

      case "cryptostocks":
        require(__DIR__ . "/../jobs/cryptostocks.php");
        break;

      case "securities_cryptostocks":
        require(__DIR__ . "/../jobs/securities_cryptostocks.php");
        break;

      case "havelock":
        require(__DIR__ . "/../jobs/havelock.php");
        break;

      case "securities_havelock":
        require(__DIR__ . "/../jobs/securities_havelock.php");
        break;

      case "cexio":
        require(__DIR__ . "/../jobs/cexio.php");
        break;

      case "crypto-trade":
        require(__DIR__ . "/../jobs/crypto-trade.php");
        break;

      case "securities_crypto-trade":
        require(__DIR__ . "/../jobs/securities_cryptotrade.php");
        break;

      case "bitstamp":
        require(__DIR__ . "/../jobs/bitstamp.php");
        break;

      case "796":
        require(__DIR__ . "/../jobs/796.php");
        break;

      case "securities_796":
        require(__DIR__ . "/../jobs/securities_796.php");
        break;

      case "justcoin":
        require(__DIR__ . "/../jobs/justcoin.php");
        break;

      case "ypool":
        require(__DIR__ . "/../jobs/ypool.php");
        break;

      case "coinbase":
        require(__DIR__ . "/../jobs/coinbase.php");
        break;

      case "litecoininvest":
        require(__DIR__ . "/../jobs/litecoininvest.php");
        break;

      case "vaultofsatoshi":
        require(__DIR__ . "/../jobs/vaultofsatoshi.php");
        break;

      case "cryptsy":
        require(__DIR__ . "/../jobs/cryptsy.php");
        break;

      case "bit2c":
        require(__DIR__ . "/../jobs/bit2c.php");
        break;

      case "kraken":
        require(__DIR__ . "/../jobs/kraken.php");
        break;

      case "bitmarket_pl":
        require(__DIR__ . "/../jobs/bitmarket_pl.php");
        break;

      case "poloniex":
        require(__DIR__ . "/../jobs/poloniex.php");
        break;

      case "anxpro":
        require(__DIR__ . "/../jobs/anxpro.php");
        break;

      case "bittrex":
        require(__DIR__ . "/../jobs/bittrex.php");
        break;

      case "westhash":
        require(__DIR__ . "/../jobs/westhash.php");
        break;

      case "btclevels":
        require(__DIR__ . "/../jobs/btclevels.php");
        break;

      case "bitnz":
        require(__DIR__ . "/../jobs/bitnz.php");
        break;

      // individual securities jobs
      case "individual_cryptostocks":
        require(__DIR__ . "/../jobs/individual_cryptostocks.php");
        break;

      case "individual_havelock":
        require(__DIR__ . "/../jobs/individual_havelock.php");
        break;

      case "individual_crypto-trade":
        require(__DIR__ . "/../jobs/individual_crypto-trade.php");
        break;

      case "individual_796":
        require(__DIR__ . "/../jobs/individual_796.php");
        break;

      case "individual_litecoininvest":
        require(__DIR__ . "/../jobs/individual_litecoininvest.php");
        break;

      // summary jobs
      case "sum":
        require(__DIR__ . "/../jobs/sum.php");
        break;

      case "securities_count":
        require(__DIR__ . "/../jobs/securities_count.php");
        break;

      // notification jobs
      case "notification":
        require(__DIR__ . "/../jobs/notification.php");
        break;

      // system jobs
      case "securities_update":
        require(__DIR__ . "/../jobs/securities_update.php");
        break;

      case "version_check":
        require(__DIR__ . "/../jobs/version_check.php");
        break;

      case "vote_coins":
        require(__DIR__ . "/../jobs/vote_coins.php");
        break;

      // transaction jobs
      case "transaction_creator":
        require(__DIR__ . "/../jobs/transaction_creator.php");
        break;

      case "transactions":
        require(__DIR__ . "/../jobs/transactions.php");
        break;

      // cleanup jobs, admin jobs etc
      case "outstanding":
        require(__DIR__ . "/../jobs/outstanding.php");
        break;

      case "expiring":
        require(__DIR__ . "/../jobs/expiring.php");
        break;

      case "expire":
        require(__DIR__ . "/../jobs/expire.php");
        break;

      case "cleanup":
        require(__DIR__ . "/../jobs/cleanup.php");
        break;

      case "disable_warning":
        require(__DIR__ . "/../jobs/disable_warning.php");
        break;

      case "disable":
        require(__DIR__ . "/../jobs/disable.php");
        break;

      case "delete_user":
        require(__DIR__ . "/../jobs/delete_user.php");
        break;

      default:
        if (substr($job['job_type'], 0, strlen("address_")) === "address_") {
          // address job
          $currency = substr($job['job_type'], strlen("address_"));
          if (!in_array($currency, get_address_currencies())) {
            throw new JobException("Currency $currency is not a valid address currency");
          }
          if (in_array($currency, \DiscoveredComponents\Currencies::getBalanceCurrencies())) {
            require(__DIR__ . "/../jobs/addresses/discovered.php");
          } else {
            // TODO eventually remove this block once we have no currencies that are also in getBalanceCurrencies()
            if (!file_exists(__DIR__ . "/../jobs/addresses/" . safe_include_arg($currency) . ".php")) {
              throw new JobException("Could not find any addresses/$currency.php include");
            }
            require(__DIR__ . "/../jobs/addresses/" . safe_include_arg($currency) . ".php");
          }
          break;
        }

        if (substr($job['job_type'], 0, strlen("blockcount_")) === "blockcount_") {
          // address job
          $currency = substr($job['job_type'], strlen("blockcount_"));
          if (!in_array($currency, \DiscoveredComponents\Currencies::getBlockCurrencies())) {
            throw new JobException("Currency $currency is not a valid block currency");
          }
          require(__DIR__ . "/../jobs/blockcount/discovered.php");
          break;
        }

        if (substr($job['job_type'], 0, strlen("difficulty_")) === "difficulty_") {
          // address job
          $currency = substr($job['job_type'], strlen("difficulty_"));
          if (!in_array($currency, \DiscoveredComponents\Currencies::getDifficultyCurrencies())) {
            throw new JobException("Currency $currency is not a valid difficulty currency");
          }
          require(__DIR__ . "/../jobs/difficulty/discovered.php");
          break;
        }

        if (substr($job['job_type'], 0, strlen("markets_")) === "markets_") {
          // address job
          $exchange = substr($job['job_type'], strlen("markets_"));
          if (!in_array($exchange, \DiscoveredComponents\Exchanges::getKeys())) {
            throw new JobException("Exchange $exchange is not a valid exchange");
          }
          require(__DIR__ . "/../jobs/markets/discovered.php");
          break;
        }

        if (substr($job['job_type'], 0, strlen("ticker_")) === "ticker_") {
          // address job
          $exchange = substr($job['job_type'], strlen("ticker_"));
          if (!in_array($exchange, \DiscoveredComponents\Exchanges::getKeys())) {
            throw new JobException("Exchange $exchange is not a valid exchange");
          }
          require(__DIR__ . "/../jobs/ticker/discovered.php");
          break;
        }

        if (substr($job['job_type'], 0, strlen("currencies_")) === "currencies_") {
          // address job
          $exchange = substr($job['job_type'], strlen("currencies_"));
          if (!in_array($exchange, \DiscoveredComponents\Accounts::getKeys())) {
            throw new JobException("Account $exchange is not a valid account");
          }
          require(__DIR__ . "/../jobs/currencies/discovered.php");
          break;
        }

        if (substr($job['job_type'], 0, strlen("hashrates_")) === "hashrates_") {
          // address job
          $exchange = substr($job['job_type'], strlen("hashrates_"));
          if (!in_array($exchange, \DiscoveredComponents\Accounts::getMiners())) {
            throw new JobException("Account $exchange is not a valid miner");
          }
          require(__DIR__ . "/../jobs/hashrates/discovered.php");
          break;
        }

        if (substr($job['job_type'], 0, strlen("account_")) === "account_") {
          // address job
          $exchange = substr($job['job_type'], strlen("account_"));
          if (!in_array($exchange, \DiscoveredComponents\Accounts::getKeys())) {
            throw new JobException("Account $exchange is not a valid account");
          }
          require(__DIR__ . "/../jobs/account/discovered.php");
          break;
        }

        // issue #12: unsafe accounts
        if (get_site_config('allow_unsafe')) {
          switch ($job['job_type']) {
            // empty for now
          }
        }

        throw new JobException("Unknown job type '" . htmlspecialchars($job['job_type']) . "'");

    }
  }

  function passed(Connection $db, Logger $logger) {
    // is this a standard job?
    $account_data = $this->findStandardJob();
    if ($account_data) {
      $logger->info("Using standard job " . print_r($account_data, true));
      if (!$account_data['failure']) {
        $logger->info("Not a failure standard job");
        return;
      }
    } else {
      return;
    }

    $failing_table = $account_data['table'];
    $job = $this->job;

    // reset the failure counter
    $q = $db->prepare("UPDATE $failing_table SET failures=0 WHERE id=?");
    $q->execute(array($job['arg_id']));
  }

  /**
   * Implements failing tables; if an account type fails multiple times,
   * then send the user an email and disable the account.
   * @see OpenclerkJobQueuer#getStandardJobs()
   */
  function failed(\Exception $runtime_exception, Connection $db, Logger $logger) {
    // is this a standard job?
    $standard = $this->findStandardJob();
    if ($standard) {
      $logger->info("Using standard job " . print_r($standard, true));
      if (!$standard['failure']) {
        $logger->info("Not a failure standard job");
        return;
      }
    } else {
      return;
    }

    $failing_table = $standard['table'];
    $job = $this->job;

    // find the relevant account_data for this standard job
    $account_data = false;
    foreach (account_data_grouped() as $label => $group) {
      foreach ($group as $exchange => $data) {
        if ($job['job_type'] == $exchange) {
          $account_data = $data;
          $account_data['exchange'] = $exchange;
          break;
        }
      }
    }
    if (!$account_data) {
      $logger->warn("Could not find any account data for job type '" . $job['job_type'] . "'");
    }
    $logger->info("Using account data " . print_r($account_data, true));

    // don't count CloudFlare as a failure
    if ($runtime_exception instanceof CloudFlareException || $runtime_exception instanceof \Openclerk\Apis\CloudFlareException) {
      $logger->info("Not increasing failure count: was a CloudFlareException");
    } else if ($runtime_exception instanceof IncapsulaException || $runtime_exception instanceof \Openclerk\Apis\IncapsulaException) {
      $logger->info("Not increasing failure count: was a IncapsulaException");
    } else if ($runtime_exception instanceof BlockchainException || $runtime_exception instanceof \Core\BlockchainException) {
      $logger->info("Not increasing failure count: was a BlockchainException");
    } else {
      $q = $db->prepare("UPDATE $failing_table SET failures=failures+1,first_failure=IF(ISNULL(first_failure), NOW(), first_failure) WHERE id=?");
      $q->execute(array($job['arg_id']));
      $logger->info("Increasing account failure count");
    }

    $user = get_user($job['user_id']);
    if (!$user) {
      $logger->info("Warning: No user " . $job['user_id'] . " found");
    } else {

      // failed too many times?
      $q = $db->prepare("SELECT * FROM $failing_table WHERE id=? LIMIT 1");
      $q->execute(array($job['arg_id']));
      $account = $q->fetch();
      $logger->info("Current account failure count: " . number_format($account['failures']));

      if ($account['failures'] >= get_premium_value($user, 'max_failures')) {
        // disable it and send an email
        $q = $db->prepare("UPDATE $failing_table SET is_disabled=1 WHERE id=?");
        $q->execute(array($job['arg_id']));

        crypto_log(print_r($account_data, true));

        if ($user['email'] && !$account['is_disabled'] /* don't send the same email multiple times */) {
          $email_type = ($job['job_type'] == "notification") ? "failure_notification" : "failure";

          send_user_email($user, $email_type, array(
            "name" => ($user['name'] ? $user['name'] : $user['email']),
            "exchange" => get_exchange_name($account_data['exchange']),
            "label" => $account_data['label'],
            "labels" => $account_data['labels'],
            "failures" => number_format($account['failures']),
            "message" => $runtime_exception->getMessage(),
            "length" => recent_format(strtotime($account['first_failure']), "", ""),
            "title" => (isset($account['title']) && $account['title']) ? "\"" . $account['title'] . "\"" : "untitled",
            "url" => absolute_url(url_for("wizard_accounts")),
          ));
          $logger->info("Sent failure e-mail to " . htmlspecialchars($user['email']) . ".");
        }

      }

    }
  }

  function findStandardJob() {
    $job = $this->job;

    // is this a standard job?
    $standard_jobs = OpenclerkJobQueuer::getStandardJobs();

    foreach ($standard_jobs as $standard) {
      if ($standard['failure'] && $standard['type'] == $job['job_type']) {
        return $standard;
      }
    }

    return false;
  }

}
