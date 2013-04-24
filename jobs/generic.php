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

// divide by 1e8 to get btc balance
$balance = crypto_get_contents(crypto_wrap_url($account['api_url']));

if (!is_numeric($balance)) {
	crypto_log("$exchange balance for " . htmlspecialchars($account['api_url']) . " is non-numeric: " . htmlspecialchars($balance));
	throw new ExternalAPIException("Generic API returned non-numeric balance");
} else {
	crypto_log("$exchange balance for " . htmlspecialchars($account['api_url']) . ": " . $balance);
}

// disable old instances
$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND account_id=:account_id");
$q->execute(array(
	"user_id" => $job['user_id'],
	"account_id" => $account['id'],
	"exchange" => $exchange,
));

// we have a balance; update the database
$q = db()->prepare("INSERT INTO balances SET user_id=:user_id, exchange=:exchange, account_id=:account_id, balance=:balance, currency=:currency, is_recent=1");
$q->execute(array(
	"user_id" => $job['user_id'],
	"account_id" => $account['id'],
	"exchange" => $exchange,
	"currency" => $account['currency'],
	"balance" => $balance,
));
crypto_log("Inserted new $exchange balances id=" . db()->lastInsertId());

