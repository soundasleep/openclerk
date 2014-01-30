<?php

/**
 * Generic API job.
 */

$exchange = "generic";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_generic WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$balance = crypto_get_contents(crypto_wrap_url($account['api_url']));

// issue #11: add editable multiplier
crypto_log("$exchange balance: $balance * " . $account['multiplier']);
$balance = $balance * $account['multiplier'];

if (!is_numeric($balance)) {
	crypto_log("$exchange balance for " . htmlspecialchars($account['api_url']) . " is non-numeric: " . htmlspecialchars($balance));
	throw new ExternalAPIException("Generic API returned non-numeric balance: " . htmlspecialchars(substr($balance, 0, 100)));
} else {
	crypto_log("$exchange balance for " . htmlspecialchars($account['api_url']) . ": " . $balance);
}

insert_new_balance($job, $account, $exchange, $account['currency'], $balance);
