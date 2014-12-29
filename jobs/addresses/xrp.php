<?php

/**
 * Ripple job (XRP).
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

if ($address['is_received']) {
	throw new JobException("Ripple cannot support getting received addresses");
}

$url = "http://s_west.ripple.com:51234/";
$input = array(
	"method" => "account_info",
	"params" => array(
		array(
			"account" => $address['address'],
			"strict" => true,
		),
	),
);

function ripple_query($path, $input) {

	$post_data = json_encode($input);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Ripple PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($path));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	if (!json_decode($res, true)) {
		throw new ExternalAPIException($res);
	}
	return crypto_json_decode($res);
}

$info = ripple_query($url, $input);
crypto_log(print_r($info, true));

if (!isset($info['result'])) {
	throw new ExternalAPIException("No result found");
}

if (isset($info['result']['error_message']) && $info['result']['error_message']) {
	throw new ExternalAPIException("Ripple returned " . htmlspecialchars($info['result']['error_message']));
}

if (!isset($info['result']['account_data']['Balance'])) {
	throw new ExternalAPIException("No balance found");
}

$balance = $info['result']['account_data']['Balance'];
$divisor = 1e6;		// divide by 1e8 to get xrp balance

crypto_log("Ripple balance for " . htmlspecialchars($address['address']) . ": " . ($balance / $divisor));

insert_new_address_balance($job, $address, $balance / $divisor);

// now look for other currencies (#242)
$input = array(
	"method" => "account_lines",
	"params" => array(
		array(
			"account" => $address['address'],
		),
	),
);

$lines = ripple_query($url, $input);
crypto_log(print_r($lines, true));

foreach ($lines['result']['lines'] as $line) {
	$cur = get_currency_key($line['currency']);
	if (in_array($cur, get_all_currencies())) {
		$balance = $line['balance'];
		$divisor = 1;

		crypto_log("Ripple ledger balance for " . htmlspecialchars($address['address']) . ": " . ($balance / $divisor) . " $cur");

		insert_new_balance($job, $address, 'ripple', $cur, $balance);
	} else {
		crypto_log("Cannot support reported Ripple currency $cur");
	}
}

