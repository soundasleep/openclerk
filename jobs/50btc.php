<?php

/**
 * 50BTC pool balance job.
 */

$exchange = "50btc";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_50btc WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// TODO try a number of times
$raw = crypto_get_contents(crypto_wrap_url("https://50btc.com/en/api/" . urlencode($account['api_key'])));
if (strpos($raw, "502 Bad Gateway") !== false) {
	throw new ExternalAPIException("502 Bad Gateway");
}
if (strpos($raw, "504 Gateway Time-out") !== false) {
	throw new ExternalAPIException("504 Gateway Time-out");
}
if (strpos($raw, "Temporary Unavailable") !== false) {
	throw new ExternalAPIException("Temporary Unavailable");
}

$data = json_decode($raw, true);
if ($data === null) {
	if (substr($raw, 0, 1) == "<") {
		throw new ExternalAPIException("Unexpectedly received HTML instead of JSON");
	} else {
		throw new ExternalAPIException("Invalid JSON detected");
	}
} else {
	if (!isset($data['user']['confirmed_rewards'])) {
		throw new ExternalAPIException("No confirmed reward found");
	}

	$balances = array('btc' => $data['user']['confirmed_rewards']);
	foreach ($balances as $currency => $balance) {

		if (!is_numeric($balance)) {
			throw new ExternalAPIException("$exchange $currency balance is not numeric");
		}
		insert_new_balance($job, $account, $exchange, $currency, $balance);
		insert_new_hashrate($job, $account, $exchange, $currency, $data['user']['hash_rate'] /* hash rates are all in MHash */);

	}

}
