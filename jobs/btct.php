<?php

/**
 * BTC Trading Co. balance job.
 * Combines the current wallet balance with the value of all securities from this account
 * (security values are done by securities_btct).
 */

$exchange = "btct";
$currency = 'btc';

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_btct WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$content = crypto_get_contents(crypto_wrap_url('https://btct.co/api/act?key=' . urlencode($account['api_key'])));
if (!$content) {
	throw new ExternalAPIException("API returned empty data");
}

// fix broken JSON
$content = preg_replace("#<!--[^>]+-->#", "", $content);

$data = json_decode($content, true);
if (!$data) {
	throw new ExternalAPIException("Invalid JSON detected.");
}

// account balance
$balance = $data['balance'][strtoupper($currency)];

// and for each security
foreach ($data['securities'] as $security => $detail) {
	// make sure that a security definition exists
	$q = db()->prepare("SELECT * FROM securities_btct WHERE name=?");
	$q->execute(array($security));
	$security_def = $q->fetch();
	if (!$security_def) {
		// need to insert a new security definition, so we can later get its value
		// we can't calculate the value of this security yet
		crypto_log("No securities_btct definition existed for '" . htmlspecialchars($security) . "': adding in new definition");
		$q = db()->prepare("INSERT INTO securities_btct SET name=?");
		$q->execute(array($security));

	} else {
		// the 'balance' for this security is the 'bid'
		$q = db()->prepare("SELECT * FROM balances WHERE exchange=:exchange AND account_id=:account_id AND is_recent=1 LIMIT 1");
		$q->execute(array(
			"exchange" => "securities_btct",
			"account_id" => $security_def['id'],
		));
		$security_value = $q->fetch();
		if (!$security_value) {
			// we can't calculate the value of this security yet
			crypto_log("Security " . htmlspecialchars($security) . " does not yet have a calculated value");

		} else {

			$calculated = $security_value['balance'] * $detail['quantity'];
			crypto_log(htmlspecialchars($security) . " @ " . htmlspecialchars($security_value['balance']) . " x " . number_format($detail['quantity']) . " = " . htmlspecialchars($calculated));

			$balance += $calculated;

		}

	}

}

// we've now calculated both the wallet balance + the value of all securities

// disable old instances
$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND currency=:currency AND account_id=:account_id");
$q->execute(array(
	"user_id" => $job['user_id'],
	"account_id" => $account['id'],
	"exchange" => $exchange,
	"currency" => $currency,
));

// we have a balance; update the database
$q = db()->prepare("INSERT INTO balances SET user_id=:user_id, exchange=:exchange, account_id=:account_id, balance=:balance, currency=:currency, is_recent=1");
$q->execute(array(
	"user_id" => $job['user_id'],
	"account_id" => $account['id'],
	"exchange" => $exchange,
	"currency" => $currency,
	"balance" => $balance,
));
crypto_log("Inserted new $exchange $currency balances id=" . db()->lastInsertId());
