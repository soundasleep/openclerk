<?php

/**
 * Litecoin balance job (BTC).
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
  throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

// get the most recent block count
$block = null;
$q = db()->prepare("SELECT * FROM blockcount_ltc WHERE is_recent=1");
$q->execute();
if ($result = $q->fetch()) {
  $block = $result['blockcount'] - \Openclerk\Config::get("ltc_confirmations");
}

$currency = \DiscoveredComponents\Currencies::getInstance("ltc");
$balance = $currency->getBalanceAtBlock($address['address'], $block, $logger);

insert_new_address_balance($job, $address, $balance);
