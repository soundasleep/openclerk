<?php

/**
 * Hypernova balance job.
 */

$exchange = "hypernova";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_hypernova WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$data = json_decode(crypto_get_contents(crypto_wrap_url("https://hypernova.pw/api/key/" . $account['api_key'] . "/")), true);
if ($data === null) {
	throw new ExternalAPIException("Invalid JSON detected (null).");
} else {
	if (!isset($data['confirmed_rewards'])) {
		throw new ExternalAPIException("No confirmed rewards found");
	}

	$balances = array('ltc' => $data['confirmed_rewards']);
	foreach ($balances as $currency => $balance) {

		if (!is_numeric($balance)) {
			throw new ExternalAPIException("$exchange $currency balance is not numeric");
		}
		insert_new_balance($job, $account, $exchange, $currency, $balance);
		insert_new_hashrate($job, $account, $exchange, $currency, $data['total_hashrate'] / 1000);

	}

}
