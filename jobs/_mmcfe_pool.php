<?php

// get the relevant address
$q = db()->prepare("SELECT * FROM " . $table . " WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$poolx = json_decode(crypto_get_contents(crypto_wrap_url($url . $account['api_key']), isset($curl_options) ? $curl_options : array()), true);
if ($poolx === null) {
	throw new ExternalAPIException("Invalid JSON detected");
} else {
	$balance = $poolx['confirmed_rewards'];

	if (!is_numeric($balance)) {
		throw new ExternalAPIException("$exchange $currency balance is not numeric");
	}
	insert_new_balance($job, $account, $exchange, $currency, $balance);

	// calculate hash rate
	// assumes all hash rates are reported by mmcfe in kh/s
	$hash_rate = 0;
	foreach ($poolx['workers'] as $name => $worker) {
		$hash_rate += $worker['hashrate'];
	}
	insert_new_hashrate($job, $account, $exchange, $currency, $hash_rate / 1000 /* hash rates are all stored in MHash */);

}
