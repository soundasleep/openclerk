<?php

/**
 * WestHash mining pool balance job.
 */

$exchange = "westhash";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_westhash WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$raw = crypto_get_contents(crypto_wrap_url("https://www.westhash.com/api?method=balance&id=" . urlencode($account['api_id']) . "&key=" . urlencode($account['api_key'])));
$data = crypto_json_decode($raw);
crypto_log(print_r($data, true));

if (isset($data['result']['error']) && $data['result']['error']) {
	throw new ExternalAPIException($data['result']['error']);
}
if (!isset($data['result']['balance_confirmed'])) {
	throw new ExternalAPIException("No confirmed balance found");
}

$currency = 'btc';
$balance = $data['result']['balance_confirmed'];
insert_new_balance($job, $account, $exchange, $currency, $balance);
