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

if ($instance instanceof \Openclerk\Currencies\BlockCurrency) {
  // get the most recent block count, to calculate confirmations
  $block = null;
  $q = db()->prepare("SELECT * FROM blockcount_" . $currency . " WHERE is_recent=1");
  $q->execute();
  if ($result = $q->fetch()) {
    $block = $result['blockcount'] - \Openclerk\Config::get($currency . "_confirmations", 6);
  }

  $balance = $instance->getBalanceAtBlock($address['address'], $block, $logger);
} else {
  // we can't do confirmations
  $balance = $currency->getBalance($address['address'], $logger);

}

insert_new_address_balance($job, $address, $balance);
