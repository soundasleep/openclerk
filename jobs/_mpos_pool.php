<?php

// get the relevant address
$q = db()->prepare("SELECT * FROM $table WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// get balance
$contents = crypto_get_contents(crypto_wrap_url($api_url . "action=getuserbalance&api_key=" . $account['api_key']), isset($curl_options) ? $curl_options : array());
if (preg_match("#^[0-9]+{#", $contents)) {
	// fix a bug with HashFaster LTC pool, which is writing the user ID at the start of the API response
	$contents = substr($contents, strpos($contents, "{"));
}
$data = crypto_json_decode($contents, "on getuserbalance");
if (!isset($data['getuserbalance']['data']['confirmed'])) {
	throw new ExternalAPIException("No confirmed balance found");
}

insert_new_balance($job, $account, $exchange, $currency, $data['getuserbalance']['data']['confirmed']);

// get hashrate
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url($api_url . "action=getuserstatus&api_key=" . $account['api_key']), isset($curl_options) ? $curl_options : array()), "on getuserstatus");
if (!isset($data['getuserstatus']['data']['hashrate'])) {
	throw new ExternalAPIException("No hashrate found");
}

insert_new_hashrate($job, $account, $exchange, $currency, $data['getuserstatus']['data']['hashrate'] / 1000 /* assume response is in KH/s */);

