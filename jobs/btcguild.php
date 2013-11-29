<?php

/**
 * BTC Guild pool balance job.
 */

$exchange = "btcguild";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_btcguild WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$raw = crypto_get_contents(crypto_wrap_url("https://www.btcguild.com/api.php?api_key=" . urlencode($account['api_key'])));
$data = crypto_json_decode($raw);

if (!isset($data['user']['unpaid_rewards'])) {
	throw new ExternalAPIException("No unpaid reward found");
}
if (!isset($data['user']['unpaid_rewards_nmc'])) {
	throw new ExternalAPIException("No unpaid NMC reward found");
}

// calculate hash rate
$hash_rate = 0;
foreach ($data['workers'] as $name => $worker) {
	$hash_rate += $worker['hash_rate'];
}

$balances = array('btc' => $data['user']['unpaid_rewards'], 'nmc' => $data['user']['unpaid_rewards_nmc']);
foreach ($balances as $currency => $balance) {

	if (!is_numeric($balance)) {
		throw new ExternalAPIException("$exchange $currency balance is not numeric");
	}
	insert_new_balance($job, $account, $exchange, $currency, $balance);
	insert_new_hashrate($job, $account, $exchange, $currency, $hash_rate);

}

