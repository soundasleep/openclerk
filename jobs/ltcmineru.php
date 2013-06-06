<?php

/**
 * LTCMine.ru balance job.
 */

$exchange = "ltcmineru";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_ltcmineru WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$data = json_decode(crypto_get_contents(crypto_wrap_url("http://ltcmine.ru/apiex?act=getuserstatsext&key=" . urlencode($account['api_key']))), true);
if ($data === null) {
	throw new ExternalAPIException("Invalid JSON detected (null).");
} else {
	if (!isset($data['account_stat']['user_balance'])) {
		throw new ExternalAPIException("No user balance found");
	}

	$balances = array('ltc' => $data['account_stat']['user_balance']);
	foreach ($balances as $currency => $balance) {

		if (!is_numeric($balance)) {
			throw new ExternalAPIException("$exchange $currency balance is not numeric");
		}
		insert_new_balance($job, $account, $exchange, $currency, $balance);

	}
}
