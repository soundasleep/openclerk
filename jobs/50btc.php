<?php

/**
 * 50BTC mining pool balance job.
 */

$exchange = "50btc";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_50btc WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://50btc.com/api/" . $account['api_key'])));

if (isset($data['error']) && $data['error']) {
	throw new ExternalAPIException($data['error']);
}

if (!isset($data['user']['confirmed_rewards'])) {
	throw new ExternalAPIException("No confirmed reward found");
}
if (!isset($data['user']['hash_rate'])) {
	throw new ExternalAPIException("No hash rate found");
}

$currency = "btc";
insert_new_balance($job, $account, $exchange, $currency, $data['user']['confirmed_rewards']);
insert_new_hashrate($job, $account, $exchange, $currency, $data['user']['hash_rate'] /* assumed to be MH/s */);
