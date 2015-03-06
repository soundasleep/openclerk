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

$instance = \DiscoveredComponents\Accounts::getInstance($exchange);

// normal balances
$balances = $instance->fetchBalances($account, $logger);
foreach ($balances as $currency => $balance) {
  insert_new_balance($job, $account, $exchange, $currency, $balance['confirmed']);

  // hashrate balances
  if (isset($balance['hashrate'])) {
    insert_new_hashrate($job, $account, $exchange, $currency, $balance['hashrate']);
  }
}
