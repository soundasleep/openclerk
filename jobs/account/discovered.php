<?php

/**
 * Balance job for an account that has been discovered through
 * DiscoveredComponents\Accounts.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$account_type = get_accounts_wizard_config($exchange);
$table = $account_type['table'];

// get the relevant account
$q = db()->prepare("SELECT * FROM $table WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find an account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$factory = new \Core\DiscoveredCurrencyFactory();
$instance = \DiscoveredComponents\Accounts::getInstance($exchange);

/**
 * Handle {@link SelfUpdatingAccount} callbacks.
 */
class SelfUpdatingAccountCallback {
  function __construct($account, $table) {
    $this->account = $account;
    $this->table = $table;
  }

  function callback($data) {
    $table = $this->table;

    $query = array();
    $args = array();
    foreach ($data as $key => $value) {
      $query[] = $key . " = :" . $key;
      $args[$key] = $value;
    }
    $args["id"] = $this->account['id'];

    crypto_log("Self-updating table '$table'");

    $q = db()->prepare("UPDATE $table SET " . implode(", ", $query) . " WHERE id=:id");
    $q->execute($args);
  }
}

if ($instance instanceof \Account\SelfUpdatingAccount) {
  $callback = new SelfUpdatingAccountCallback($account, $table);
  $instance->registerAccountUpdateCallback(array($callback, 'callback'));
}

// normal balances
$balances = $instance->fetchBalances($account, $factory, $logger);

foreach ($balances as $currency => $balance) {
  // only store currencies we are actually interested in
  if (in_array($currency, \DiscoveredComponents\Currencies::getKeys())) {
    // some accounts, e.g. ghash, don't return a balance
    if (isset($balance['confirmed'])) {
      insert_new_balance($job, $account, $exchange, $currency, $balance['confirmed']);
    }

    // hashrate balances
    if (isset($balance['hashrate'])) {
      insert_new_hashrate($job, $account, $exchange, $currency, $balance['hashrate'] / 1e6 /* H/s into MH/s */);
    }
  }
}

