<?php

/**
 * Summary job: total NVC.
 */

$currency = 'nvc';

// get the most recent blockchain balances
$q = db()->prepare("SELECT * FROM address_balances
	JOIN addresses ON address_balances.address_id=addresses.id
	WHERE address_balances.user_id=? AND is_recent=1 AND currency=?
	GROUP BY address_id");	// group by address_id to prevent race conditions
$q->execute(array($job['user_id'], $currency));
$total_blockchain_balance = 0;
while ($balance = $q->fetch()) {
	$total += $balance['balance'];
	$total_blockchain_balance += $balance['balance'];
}

// and the most recent offsets
$q = db()->prepare("SELECT * FROM offsets
	WHERE user_id=? AND is_recent=1 AND currency=?");
$q->execute(array($job['user_id'], $currency));
$total_offsets_balance = 0;
while ($offset = $q->fetch()) { // we should only have one anyway
	$total += $offset['balance'];
	$total_offsets_balance += $offset['balance'];
}

// and the most recent exchange/API balances
$q = db()->prepare("SELECT * FROM balances
	WHERE user_id=? AND is_recent=1 AND currency=?
	GROUP BY exchange, account_id");	// group by exchange/account_id to prevent race conditions
$q->execute(array($job['user_id'], $currency));
while ($offset = $q->fetch()) { // we should only have one anyway
	$total += $offset['balance'];
}

crypto_log("Total $currency balance for user " . $job['user_id'] . ": " . $total);
