<?php

// get the relevant address
$q = db()->prepare("SELECT * FROM " . $table . " WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$poolx = json_decode(crypto_get_contents(crypto_wrap_url($url . $account['api_key'])), true);
if ($poolx === null) {
	throw new ExternalAPIException("Invalid JSON detected (null).");
} else {
	$balance = $poolx['confirmed_rewards'];

	if (!is_numeric($balance)) {
		throw new ExternalAPIException("$exchange $currency balance is not numeric");
	}
	insert_new_balance($job, $account, $exchange, $currency, $balance);

	// calculate hash rate
	$hash_rate = 0;
	foreach ($poolx['workers'] as $name => $data) {
		$hash_rate += $data['hashrate'];
	}
	insert_new_balance($job, $account, $exchange, "mh", $hash_rate / 1000 /* hash rates are all in MHash */);

}
