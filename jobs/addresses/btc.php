<?php

/**
 * Blockchain job (BTC).
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$currency = \DiscoveredComponents\Currencies::getInstance("btc");
$balance = $currency->getBalance($address['address'], $logger);

insert_new_address_balance($job, $address, $balance);

