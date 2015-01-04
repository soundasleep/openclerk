<?php

/**
 * Address job for a currency that has been discovered through
 * DiscoveredComponents\Currencies.
 */

if (!$currency) {
  throw new JobException("No currency defined");
}

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$instance = \DiscoveredComponents\Currencies::getInstance($currency);

if ($instance instanceof \Openclerk\Currencies\ConfirmableCurrency) {
  // directly request confirmations
  $balance = $instance->getBalanceWithConfirmations($address['address'], \Openclerk\Config::get($currency . "_confirmations", 6), $logger);
  insert_new_address_balance($job, $address, $balance);

} else if ($instance instanceof \Openclerk\Currencies\BlockBalanceableCurrency) {
  // get the most recent block count, to calculate confirmations
  $block = null;
  $q = db()->prepare("SELECT * FROM blockcount_" . $currency . " WHERE is_recent=1");
  $q->execute();
  if ($result = $q->fetch()) {
    $block = $result['blockcount'] - \Openclerk\Config::get($currency . "_confirmations", 6);
  }

  $balance = $instance->getBalanceAtBlock($address['address'], $block, $logger);
  insert_new_address_balance($job, $address, $balance);

} else {
  // we can't do confirmations or block balances
  $balance = $instance->getBalance($address['address'], $logger);
  insert_new_address_balance($job, $address, $balance);

}

if ($instance instanceof \Openclerk\Currencies\MultiBalanceableCurrency) {
  $balances = $instance->getMultiBalances($address['address'], $logger);
  foreach ($balances as $code => $balance) {
    if (in_array($code, get_all_currencies())) {
      if ($code != $currency) {
        // skip balances we've already inserted for this currency
        insert_new_balance($job, $address, 'ripple', $code, $balance);
      }
    } else {
      $logger->info("Unknown multi currency '$code'");
    }
  }

}
