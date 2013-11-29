<?php

/**
 * LiteGuardian balance job.
 */

$exchange = "liteguardian";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_liteguardian WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$currency = 'ltc';

$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.liteguardian.com/api/" . $account['api_key'])));

if (!isset($data['balance'])) {
	throw new ExternalAPIException("No balance found");
}
if (!isset($data['hashrate'])) {
	throw new ExternalAPIException("No hashrate found");
}

insert_new_balance($job, $account, $exchange, $currency, $data['balance']);
insert_new_hashrate($job, $account, $exchange, $currency, $data['hashrate'] / 1000 /* API returns KHash */);
