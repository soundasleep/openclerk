<?php

/**
 * 796 security value job.
 * Retrieves the current 'bid' value for a particular security.
 */

$exchange = "securities_796";
$currency = 'btc';

// get the relevant address
$q = db()->prepare("SELECT * FROM securities_796 WHERE id=?");
$q->execute(array($job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id']);
}

$content = crypto_get_contents(crypto_wrap_url('http://api.796.com/v3/stock/ticker.html?type=' . urlencode($account['api_name'])));
if (!$content) {
	throw new ExternalAPIException("API returned empty data");
}

$data = crypto_json_decode($content);

// also available: last, high, low, vol, buy, sell
if (!isset($data['ticker']['last'])) {
	throw new ExternalAPIException("External API returned no last price");
}
$balance = $data['ticker']['last'];
crypto_log("Last price for " . htmlspecialchars($account['name']) . ": " . $balance);

insert_new_balance($job, $account, $exchange, $currency, $balance);
