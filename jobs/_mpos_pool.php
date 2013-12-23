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
	// necessary check only for HashFaster LTC pool
	if (!isset($data['getuserbalance']['confirmed'])) {

		// this will also trigger if 'confirmed' is null (isset() also checks it is not null)
		if (strpos($contents, '"confirmed":') !== false) {
			// if 'confirmed' is actually in the response,
			// (a bit of a hack for an isset() but also null check)
			$include_balance = false;
			crypto_log("Confirmed balance was null: ignoring");
		} else {
			throw new ExternalAPIException("No confirmed balance found");
		}

	} else {
		insert_new_balance($job, $account, $exchange, $currency, $data['getuserbalance']['confirmed']);
	}
} else {
	insert_new_balance($job, $account, $exchange, $currency, $data['getuserbalance']['data']['confirmed']);
}

// get hashrate
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url($api_url . "action=getuserstatus&api_key=" . $account['api_key']), isset($curl_options) ? $curl_options : array()), "on getuserstatus");
if (!isset($data['getuserstatus']['data']['hashrate'])) {
	// necessary check only for HashFaster LTC pool
	if (!isset($data['getuserstatus']['hashrate'])) {
		throw new ExternalAPIException("No hashrate found");
	} else {
		insert_new_hashrate($job, $account, $exchange, $currency, $data['getuserstatus']['hashrate'] / 1000 /* assume response is in KH/s */);
	}
} else {
	insert_new_hashrate($job, $account, $exchange, $currency, $data['getuserstatus']['data']['hashrate'] / 1000 /* assume response is in KH/s */);
}

