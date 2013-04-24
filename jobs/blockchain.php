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

// divide by 1e8 to get btc balance
$balance = crypto_get_contents(crypto_wrap_url("http://blockchain.info/q/addressbalance/" . urlencode($address['address']) . "?confirmations=6"));
$divisor = 1e8;

if (!is_numeric($balance)) {
	crypto_log("Blockchain balance for " . htmlspecialchars($address['address']) . " is non-numeric: " . htmlspecialchars($balance));
	throw new ExternalAPIException("Blockchain returned non-numeric balance");
} else {
	crypto_log("Blockchain balance for " . htmlspecialchars($address['address']) . ": " . ($balance / $divisor));
}

// disable old instances
$q = db()->prepare("UPDATE address_balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND address_id=:address_id");
$q->execute(array(
	"user_id" => $job['user_id'],
	"address_id" => $address['id'],
));

// we have a balance; update the database
$q = db()->prepare("INSERT INTO address_balances SET user_id=:user_id, address_id=:address_id, balance=:balance / :divisor, is_recent=1");
$q->execute(array(
	"user_id" => $job['user_id'],
	"address_id" => $address['id'],
	"balance" => $balance,
	"divisor" => $divisor,
));
crypto_log("Inserted new address_balances id=" . db()->lastInsertId());

