<?php

/**
 * litecoinpool.org pool balance job.
 */

$exchange = "litecoinpool";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_litecoinpool WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.litecoinpool.org/api?api_key=" . urlencode($account['api_key']))));

if (!isset($data['user'])) {
	throw new ExternalAPIException("No user in response");
}
if (!isset($data['user']['hash_rate'])) {
	throw new ExternalAPIException("No hash_rate in response");
}
if (!isset($data['user']['unpaid_rewards'])) {
	throw new ExternalAPIException("No unpaid_rewards in response");
}

$currency = 'ltc';

insert_new_balance($job, $account, $exchange, $currency, $data['user']['unpaid_rewards']);
insert_new_hashrate($job, $account, $exchange, $currency, $data['user']['hash_rate'] / 1000 /* hash rates are all in MHash */);

