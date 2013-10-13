<?php

/**
 * Havelock Investments security value job.
 * Combines the current wallet balance with the value of all securities from this account
 * (security values are done by securities_btct).
 */

$exchange = "securities_havelock";
$currency = 'btc';

// get the relevant address
$q = db()->prepare("SELECT * FROM securities_havelock WHERE id=?");
$q->execute(array($job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id']);
}

require(__DIR__ . "/_havelock.php");

$content = havelock_query("https://www.havelockinvestments.com/r/tickerfull", array('symbol' => $account['name']));
crypto_log("Last price for " . htmlspecialchars($account['name']) . ": " . $content[$account['name']]['last']);
$balance = $content[$account['name']]['last'];

insert_new_balance($job, $account, $exchange, $currency, $balance);
