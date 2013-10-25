<?php

/**
 * Individual Crypto-Trade securities job.
 */

$exchange = "individual_crypto-trade";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_individual_cryptotrade WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// get the most recent ticker balance for this security
$q = db()->prepare("SELECT * FROM balances WHERE exchange=? AND account_id=? AND is_recent=1 LIMIT 1");
$q->execute(array('securities_crypto-trade', $account['security_id']));
$ticker = $q->fetch();

if (!$ticker) {
	throw new ExternalAPIException("Could not find any recent ticker balance for securities_crypto-trade ID=" . htmlspecialchars($account['security_id']));
} else {
	$calculated = $ticker['balance'] * $account['quantity'];
	crypto_log('security ' . htmlspecialchars($account['security_id']) . " @ " . htmlspecialchars($ticker['balance']) . " x " . number_format($account['quantity']) . " = " . htmlspecialchars($calculated));

	insert_new_balance($job, $account, $exchange, $ticker['currency'], $calculated);
}
