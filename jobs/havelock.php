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

require("_havelock.php");

$content = havelock_query("https://www.havelockinvestments.com/r/balance", array('key' => $account['api_key']));

// balance, balanceavailable, balanceescrow
$balance = $content['balance']['balance'];
crypto_log("$exchange wallet balance for " . $job['user_id'] . ": " . $balance);

// assume we don't need to delay
$content = havelock_query("https://www.havelockinvestments.com/r/portfolio", array('key' => $account['api_key']));
if ($content['portfolio'] && is_array($content['portfolio'])) {
	foreach ($content['portfolio'] as $entry) {
		// the API returns the marketvalue, so we can just use that rather than calculate it from previous jobs (like btct)
		crypto_log("$exchange security balance for " . htmlspecialchars($entry['symbol']) . ": " . $entry['quantity'] . '*' . $entry['lastprice'] . "=" . $entry['marketvalue']);
		$balance += $entry['marketvalue'];
	}
}

// we've now calculated both the wallet balance + the value of all securities
insert_new_balance($job, $account, $exchange, $currency, $balance);
