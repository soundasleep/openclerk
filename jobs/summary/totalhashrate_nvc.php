<?php

/**
 * Summary job: total MHash/s going towards NVC.
 */

// get the most recent exchange/API balances
$q = db()->prepare("SELECT * FROM hashrates
	WHERE user_id=? AND is_recent=1 AND currency=?
	GROUP BY exchange, account_id");	// group by exchange/account_id to prevent race conditions
$q->execute(array($job['user_id'], 'nvc'));
while ($offset = $q->fetch()) { // we should only have one anyway
	$total += $offset['mhash'];
}

crypto_log("Total NVC MHash/s for user " . $job['user_id'] . ": " . $total);
