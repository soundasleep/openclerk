<?php

/**
 * Slush's pool balance job.
 */

$exchange = "slush";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_slush WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$data = json_decode(crypto_get_contents(crypto_wrap_url("https://mining.bitcoin.cz/accounts/profile/json/" . $account['api_token'])), true);
if ($data === null) {
	throw new ExternalAPIException("Invalid JSON detected");
} else {
	if (!isset($data['confirmed_reward'])) {
		throw new ExternalAPIException("No confirmed reward found");
	}
	if (!isset($data['confirmed_nmc_reward'])) {
		throw new ExternalAPIException("No confirmed NMC reward found");
	}

	$balances = array('btc' => $data['confirmed_reward'], 'nmc' => $data['confirmed_nmc_reward']);
	foreach ($balances as $currency => $balance) {

		if (!is_numeric($balance)) {
			throw new ExternalAPIException("$exchange $currency balance is not numeric");
		}
		insert_new_balance($job, $account, $exchange, $currency, $balance);
		insert_new_hashrate($job, $account, $exchange, $currency, $data['hashrate']);

	}

}
