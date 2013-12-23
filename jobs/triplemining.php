<?php

/**
 * TripleMining pool balance job.
 */

$exchange = "triplemining";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_triplemining WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://api.triplemining.com/json/" . $account['api_key'])));

if (!isset($data['confirmed_reward'])) {
	throw new ExternalAPIException("No confirmed reward found");
}
if (!is_numeric($data['confirmed_reward'])) {
	throw new ExternalAPIException("Confirmed reward is not numeric");
}
if (!isset($data['hashrate'])) {
	throw new ExternalAPIException("No hashrate found");
}

$currency = 'btc';
insert_new_balance($job, $account, $exchange, $currency, $data['confirmed_reward']);
insert_new_hashrate($job, $account, $exchange, $currency, $data['hashrate']);
