<?php

/**
 * Summary job: total NMC.
 */

// get the most recent blockchain balances
$q = db()->prepare("SELECT * FROM address_balances
	JOIN addresses ON address_balances.address_id=addresses.id
	WHERE address_balances.user_id=? AND is_recent=1 AND currency=?");
$q->execute(array($job['user_id'], 'nmc'));
while ($balance = $q->fetch()) {
	$total += $balance['balance'];
}

// and the most recent offsets
$q = db()->prepare("SELECT * FROM offsets
	WHERE user_id=? AND is_recent=1 AND currency=?");
$q->execute(array($job['user_id'], 'nmc'));
while ($offset = $q->fetch()) { // we should only have one anyway
	$total += $offset['balance'];
}

crypto_log("Total NMC balance for user " . $job['user_id'] . ": " . $total);
