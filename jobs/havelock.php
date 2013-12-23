<?php

/**
 * Havelock Investments balance job.
 * Combines the current wallet balance with the value of all securities from this account
 * (security values are done by securities_btct).
 */

$exchange = "havelock";
$currency = 'btc';

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_havelock WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

require(__DIR__ . "/_havelock.php");

$content = havelock_query("https://www.havelockinvestments.com/r/balance", array('key' => $account['api_key']));

// balance, balanceavailable, balanceescrow
$wallet = $content['balance']['balance'];
$balance = 0;
crypto_log("$exchange wallet balance for " . $job['user_id'] . ": " . $wallet);

// set is_recent=0 for all old security instances for this user
$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=? AND account_id=?");
$q->execute(array($job['user_id'], $exchange, $account['id']));

// assume we don't need to delay
$content = havelock_query("https://www.havelockinvestments.com/r/portfolio", array('key' => $account['api_key']));
if ($content['portfolio'] && is_array($content['portfolio'])) {
	foreach ($content['portfolio'] as $entry) {
		// the API returns the marketvalue, so we can just use that rather than calculate it from previous jobs (like btct)
		crypto_log("$exchange security balance for " . htmlspecialchars($entry['symbol']) . ": " . $entry['quantity'] . '*' . $entry['lastprice'] . "=" . $entry['marketvalue']);
		$balance += $entry['marketvalue'];

		// find the security ID, if there is one
		$q = db()->prepare("SELECT * FROM securities_havelock WHERE name=?");
		$q->execute(array($entry['symbol']));
		$security_def = $q->fetch();
		if ($security_def) {
			// insert security instance
			$q = db()->prepare("INSERT INTO securities SET user_id=:user_id, exchange=:exchange, security_id=:security_id, quantity=:quantity, account_id=:account_id, is_recent=1");
			$q->execute(array(
				'user_id' => $job['user_id'],
				'exchange' => $exchange,
				'security_id' => $security_def['id'],
				'quantity' => $entry['quantity'],
				'account_id' => $account['id'],
			));
		}
	}
}

// we've now calculated both the wallet balance + the value of all securities
insert_new_balance($job, $account, $exchange . "_wallet", $currency, $wallet);
insert_new_balance($job, $account, $exchange . "_securities", $currency, $balance);
